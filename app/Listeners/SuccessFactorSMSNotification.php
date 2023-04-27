<?php

namespace App\Listeners;

use App\Classes\External\IranSms;
use App\Events\successFactor;

class SuccessFactorSMSNotification
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
     * @param successFactor $event
     * @return void
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function handle(successFactor $event)
    {
        $userMobile = $event->user->phone;
        $resnumber = $event->resnumber;
        if (isset($userMobile)) {
            $body = [
                'param1' => $resnumber,
            ];
            IranSms::sendSingleSms('factorsuccess',$userMobile,$body);
        }
    }
}
