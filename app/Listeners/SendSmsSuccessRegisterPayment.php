<?php

namespace App\Listeners;

use App\Classes\External\IranSms;
use App\Events\smsNotAnswer;
use App\Events\smsSuccessRegisterPayment;


class SendSmsSuccessRegisterPayment
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
     * @param smsNotAnswer $event
     * @return void
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function handle(smsSuccessRegisterPayment $event)
    {
        $userMobile = $event->factor->user->phone;
        $supporter_family = $event->supporter_user->family;
        $factor_resnumber = $event->factor->resnumber;
        if (isset($userMobile)) {
            $body = [
                'param1' => $factor_resnumber,
                'param2' => $supporter_family,

            ];
            IranSms::sendSingleSms('sendsmssuccessregisterpayment',$userMobile,$body);

        }
    }
}
