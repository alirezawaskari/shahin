<?php

namespace App\Listeners;

use App\Classes\External\IranSms;
use App\Classes\External\SendSms;
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
            $setting = Setting::first();
            if ( $event->type == 'online' ) {
                $body = [
                    'param1' => $factor,
                ];
                SendSms::sendSingleSms('sendlinkfactor', $userMobile, $body);
            } elseif ( $event->type == 'send_card_number' ) {
                $body = [
                    'param1' => number_format( $event->factor->price ),
                    'param2' => $setting->card_number,
                    'param3' => $setting->shaba_number,
                    'param4' => $setting->card_holder,
                ];
                SendSms::sendSingleSms('sendcardnumber', $userMobile, $body);
            } elseif( $event->type == 'tax_free_card')
            {
                $body = [
                    'param1' => number_format( $event->factor->price ),
                    'param2' => $setting->tax_free_card_number,
                    'param3' => $setting->tax_free_shaba_number,
                    'param4' => $setting->tax_free_card_holder,
                ];
                SendSms::sendSingleSms('sendcardnumber', $userMobile, $body);
            }
        }
    }
}
