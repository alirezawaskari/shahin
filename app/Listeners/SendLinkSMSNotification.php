<?php

namespace App\Listeners;

use App\Classes\External\IranSms;
use App\Events\sendlinkFactor;
use App\Models\Setting;

class SendLinkSMSNotification {
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
    * @param fastserviceCallcenter $event
    * @return void
    * @throws \GuzzleHttp\Exception\GuzzleException
    */

    public function handle( sendlinkFactor $event )
	{
        $userMobile = $event->user->phone;
        $factor = $event->factor->resnumber;
//        $callcenter = $event->callcenter;

        if ( isset( $userMobile ) ) {

            if ( $event->type == 'online' ) {
                $body = [
                    'param1' => $factor,
                ];
                IranSms::sendSingleSms('sendlinkfactor',$userMobile,$body);

            } elseif ( $event->type == 'send_card_number' ) {
                $settings = Setting::firstorfail();
                $param3 = ' به شماره کارت : '. $settings->card_number .' به نام ' . $settings->card_holder;
                $body = [
                    'param1' => number_format( $event->factor->price ),
                    'param2' => $param3
                ];
                IranSms::sendSingleSms('sendcardnumber',$userMobile,$body);
            } elseif ( $event->type == 'send_shaba_number' ) {
                $settings = Setting::firstorfail();
                $param3 = ' به شماره شبا : '. $settings->shaba_number .' به نام ' . $settings->card_holder;
                $body = [
                    'param1' => number_format( $event->factor->price ),
                    'param2' => $param3
                ];
                IranSms::sendSingleSms('sendcardnumber',$userMobile,$body);
            }
        }
    }
}
