<?php

namespace App\Listeners;

use App\Classes\External\IranSms;
use App\Classes\External\SendSms;
use App\Events\fastregisterorganCallcenter;
class RegOrganSMSNotification
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
     * @param fastregisterorganCallcenter $event
     * @return void
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function handle(fastregisterorganCallcenter $event)
    {
        $userMobile = $event->user->phone;
        $pass = $event->pass;
        $userWallet = $event->user->wallet;

        if (isset($userMobile)) {
            $body = [
                'param1' => $userMobile,
                'param2' => $pass,
                'param3' => $userWallet,
            ];
            SendSms::sendSingleSms('organizationfastregister',$userMobile,$body);

        }
    }
}
