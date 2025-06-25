<?php

namespace App\Listeners;

use App\Classes\External\IranSms;
use App\Classes\External\SendSms;
use App\Events\sendlinkFactor;


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
                SendSms::sendSingleSms('sendlinkfactor',$userMobile,$body);

            } elseif ( $event->type == 'card' ) {
                $param3 = ' به شماره کارت : '.' 8174 6519 0610 5047 '.' به نام سید مصطفی میر حسینی ' ;
                $body = [
                    'param1' => 'هزینه',
                    'param2' => number_format( $event->factor->price ),
                    'param3' => $param3
                ];
                SendSms::sendSingleSms('sendcardnumber',$userMobile,$body);

            }
        }
    }
}
