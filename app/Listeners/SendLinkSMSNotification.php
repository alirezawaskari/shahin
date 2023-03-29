<?php

namespace App\Listeners;

use App\Events\sendlinkFactor;
use GuzzleHttp\Client;


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
        $callcenter = $event->callcenter;

        if ( isset( $userMobile ) ) {
            $headers = [
                'apikey' => config( 'services.sms.apikey' ),
                'Content-Type' => 'application/x-www-form-urlencoded'
            ];
            $client = new Client( [
                'headers' => $headers
            ] );
            if ( $event->type == 'online' ) {
                $body = [
                    'receptor' => $userMobile,
                    'type' => 1,
                    'template' => 'sendlinkfactor',
                    'param1' => $factor,
                ];
                $client->request( 'POST', 'http://api.parsasms.com/v2/send/verify', [
                    'form_params' => $body
                ] );
            } elseif ( $event->type == 'card' ) {
                $param3 = ' به شماره کارت : '.' 8174 6519 0610 5047 '.' به نام سید مصطفی میر حسینی ' ;
                $body = [
                    'receptor' => $userMobile,
                    'type' => 1,
                    'template' => 'sendcardnumber',
                    'param1' => 'هزینه',
                    'param2' => number_format( $event->factor->price ),
                    'param3' => $param3
                ];

                $client->request( 'POST', 'http://api.parsasms.com/v2/send/verify', [
                    'form_params' => $body
                ] );
            }
        }
    }
}
