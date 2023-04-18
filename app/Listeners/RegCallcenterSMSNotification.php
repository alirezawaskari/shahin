<?php

namespace App\Listeners;

use App\Classes\External\IranSms;
use App\Events\fastregisterCallcenter;


class RegCallcenterSMSNotification
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param fastregisterCallcenter $event
     * @return void
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function handle(fastregisterCallcenter $event)
    {
        $userMobile = $event->user->phone;
        $pass = $event->pass;
        if (isset($userMobile)) {
            $body = [
                'param1' => $userMobile,
                'param2' => $pass,
            ];
            IranSms::sendSingleSms('callcenterfastregister',$userMobile,$body);

        }
    }
}
