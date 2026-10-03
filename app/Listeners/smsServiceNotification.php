<?php

namespace App\Listeners;

use App\Classes\External\SendSms;
use App\Events\smsService;
use App\Models\FactorItem;
use App\Models\Service;
use GuzzleHttp\Exception\GuzzleException;

class smsServiceNotification {
    /**
    * Handle the event.
    *
    * @param smsService $event
    * @return void
    * @throws GuzzleException
    */

    public function handle( smsService $event ) {
        $factorItem = $event->factorItem;
        $userMobile = $factorItem->factor->user->phone;

        $type = $event->type;
        switch( $type ) {
            case 'setCenter':
            self::sendSetCenterNotification( $factorItem, $userMobile );
            break;
            case 'turnRatings':
            self::sendTurnRatingsNotification( $factorItem, $userMobile );
            break;
            case 'setRecordChanges':
            self::sendSetRecordChangesNotification( $factorItem, $userMobile );
            break;
            case 'rejectSession':
            self::sendRejectSessionNotification( $factorItem, $userMobile );
            break;
        }

    }
    /**
     * Summary of sendSetCenterNotification
     * @param FactorItem $factorItem
     * @param mixed $userMobile
     * @return void
     */
    private function sendSetCenterNotification( FactorItem $factorItem, $userMobile ) {
        $center_title = $factorItem->center->title;
        $center_call = $factorItem->center->phone;
        $center_address = $factorItem->center->address;
        $factoritemable = FactorItem::getFactorItemAble( $factorItem, [] );
        $body = [
            'param1' => implode(',', $factoritemable),
            'param2' => $center_title,
            'param3' => $center_call,
            // 'param4' => $center_address,
        ];
        SendSms::sendSingleSms( 'setCenterUserNotification', $userMobile, $body );
    }
    /**
     * Summary of sendSetCenterNotification
     * @param FactorItem $factorItem
     * @param mixed $userMobile
     * @return void
     */
    private function sendTurnRatingsNotification( FactorItem $factorItem, $userMobile ) {
        if(isset($factorItem['factor_values']['typeConsultation']) && $factorItem['factor_values']['typeConsultation'] == 'online')
        {
            self::sendTurnRatingsNotificationOnline($factorItem, $userMobile);
        }
        else
        {
            self::sendTurnRatingsNotificationInPerson($factorItem, $userMobile);
        }
    }
    /**
     * Summary of sendSetCenterNotification
     * @param FactorItem $factorItem
     * @param mixed $userMobile
     * @return void
     */
    private function sendRejectSessionNotification( FactorItem $factorItem, $userMobile ) {
        $center_title = $factorItem->center->title;
        $user = $factorItem->factor->user->name.' '.$factorItem->factor->user->family;
        $center_call = $factorItem->center->phone;
        $supporter_phone = $factorItem->supporter ? $factorItem->supporter->employee->business_phone : $center_call;
     
        $body = [
            'param1' => $user,
            'param2' => $center_title,
            'param3' => $supporter_phone,
        ];
        SendSms::sendSingleSms( 'rejectSessionNotification', $userMobile, $body );
    }
    /**
     * Summary of sendSetRecordChangesNotification
     * @param FactorItem $factorItem
     * @param mixed $userMobile
     * @return void
     */
    private function sendSetRecordChangesNotification( FactorItem $factorItem, $userMobile ) {
        $factoritemable = FactorItem::getFactorItemAble( $factorItem, [] );
        $body = [
            'param1' => implode(',', $factoritemable),
            'param2' => 'https://lalecard.ir/factorItem/'.$factorItem->resnumber.'#recommendations',
        ];
        SendSms::sendSingleSms( 'setRecordChangesNotification', $userMobile, $body );
    }
    /**
     * @param \App\Models\FactorItem $factorItem
     * @param mixed $userMobile
     * @return void
     */
    private function sendTurnRatingsNotificationInPerson( FactorItem $factorItem, $userMobile )
    {
        $user = $factorItem->factor->user->name.' '.$factorItem->factor->user->family;
        $center_title = $factorItem->center->title;
        $center_call = $factorItem->center->phone;
        $center_address = $factorItem->center->address;
        $factoritemable = ($factorItem->factoritemable_type == Service::class ? 'خدمات ' : 'کالا ') . $factorItem->factoritemable->category->title;
        $date = verta($factorItem->date)->format('Y/m/d H:i:s');
        $time = verta($factorItem->date)->format('H:i');
        $supporter_name = $factorItem->supporter ? $factorItem->supporter->family : '';
        $body = [
            'param1' => $user,
            'param2' => $factoritemable,
            'param3' => $center_title,
            'param4' => $date,
            'param5' => $time,
            'param6' => $supporter_name,
            'param7' => $center_address,
            'param8' => $center_call,
        ];
        SendSms::sendSingleSms( 'centerTurnRaitingInPerson', $userMobile, $body );
    }
    /**
     * @param \App\Models\FactorItem $factorItem
     * @param mixed $userMobile
     * @return void
     */
    private function sendTurnRatingsNotificationOnline( FactorItem $factorItem, $userMobile )
    {
        $center_title = $factorItem->center->title;
        $user = $factorItem->factor->user->name.' '.$factorItem->factor->user->family;
        $date = verta($factorItem->date)->format('Y/m/d H:i:s');
        $time = verta($factorItem->date)->format('H:i');
        $center_call = $factorItem->center->phone;
        $supporter_phone = $factorItem->supporter ? $factorItem->supporter->employee->business_phone : $center_call;
        $supporter_name = $factorItem->supporter ? $factorItem->supporter->family : '';

        $body = [
            'param1' => $user,
            'param2' => $center_title,
            'param3' => $date,
            'param4' => $time,
            'param5' => $supporter_phone,
            'param6' => $supporter_name,
        ];
        SendSms::sendSingleSms( 'centerTurnRaitingOnline', $userMobile, $body );
    }
}
