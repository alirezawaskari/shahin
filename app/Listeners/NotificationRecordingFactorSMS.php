<?php

namespace App\Listeners;

use App\Classes\External\SendSms;
use App\Events\notificationRecordingFactor;
use App\Models\FactorItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class NotificationRecordingFactorSMS {
    /**
    * Create the event listener.
    *
    * @return void
    */

    public function __construct() {
        //
    }

    /**
    * Handle the event.
    *
    * @param notificationRecordingFactor $event
    * @return void
    * @throws \GuzzleHttp\Exception\GuzzleException
    */

    public function handle( notificationRecordingFactor $event ) {
        $factor_item = $event->factorItem;
        $resnumber = $factor_item->resnumber;
        self::sendSmsNotificationRecordingFactor( $factor_item, $resnumber );
        self::sendSmsNotificationRecordingFactorConfirmation($factor_item , $resnumber);
    }
    /**
    * Summary of sendSmsNotificationRecordingFactor
    * @param \App\Models\FactorItem $factorItem
    * @param mixed $resnumber
    * @return void
    */

    private function sendSmsNotificationRecordingFactor( FactorItem $factorItem , $resnumber ) {
        $factor_item_able = $factorItem->factoritemable->title;
        $user = $factorItem->factor->user;
        $pass = rand( 123456, 999999 );
        $pass = Hash::make( value: $pass );
        $user->update( [ 'password' => $pass ] );
        $userMobile = $user->phone;
        if ( isset( $userMobile ) ) {

            $access = 'شماره موبایل : ' . $user->phone .' و رمز عبور :'. $pass;
            $body = [
                'param1' => $factor_item_able,
                'param2' => $access,
                'param3' => 'lalecard.ir/factorItem/'.$resnumber,
            ];
            SendSms::sendSingleSms( 'notificationrecordingfactor', $userMobile, $body );
        }
    }
    /**
     * Summary of sendSmsNotificationRecordingFactorConfirmation
     * @param \App\Models\FactorItem $factorItem
     * @param mixed $resnumber
     * @return void
     */
    private function sendSmsNotificationRecordingFactorConfirmation(FactorItem $factorItem , $resnumber) {
        $user = $factorItem->supervisor->factor->user;
        $userMobile = $user->phone;
        $center_call = $factorItem->center->phone;
        $supporter_phone = $factorItem->supporter ? $factorItem->supporter->employee->business_phone : $center_call;
     
        if ( isset( $userMobile ) ) {

            $body = [
                'param1' => $user->name.' '.$user->family,
                'param2' => $resnumber,
                'param3' => $supporter_phone,
                'param4' => 'lalecard.ir/factorItem/factor/'.$resnumber,
            ];
            SendSms::sendSingleSms( 'notificationrecordingfactorforconfirmation', $userMobile, $body );
        }
    }
}
