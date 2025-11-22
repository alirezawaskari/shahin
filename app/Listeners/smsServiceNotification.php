<?php

namespace App\Listeners;

use App\Classes\External\IranSms;
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
        $factoritemable = FactorItem::getFactorItemAble( $factorItem, [] );
        $body = [
            'param1' => $factoritemable,
            'param2' => $center_title,
            'param3' => $center_call,
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
        $center_title = $factorItem->center->title;
        $center_call = $factorItem->center->phone;
        $center_address = $factorItem->center->address;
        $factoritemable = ($factorItem->factoritemable_type == Service::class ? 'خدمات ' : 'کالا ') . $factorItem->factoritemable->category->title;
        $body = [
            'param1' => $factoritemable,
            'param2' => $center_title,
            'param3' => $center_call,
            'param4' => $center_address,
        ];
        SendSms::sendSingleSms( 'centerTurnRaiting', $userMobile, $body );
    }
    /**
     * Summary of sendSetCenterNotification
     * @param FactorItem $factorItem
     * @param mixed $userMobile
     * @return void
     */
    private function sendRejectSessionNotification( FactorItem $factorItem, $userMobile ) {
        $center_title = $factorItem->center->title;
        $body = [
            'param1' => $center_title,
        ];
        SendSms::sendSingleSms( 'rejectSessionNotification', $userMobile, $body );
    }
}
