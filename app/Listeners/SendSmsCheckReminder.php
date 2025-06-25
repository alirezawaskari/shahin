<?php

namespace App\Listeners;

use App\Classes\External\IranSms;
use App\Classes\External\SendSms;
use App\Events\smsNotAnswer;
use App\Events\smsSuccessRegisterPayment;

class SendSmsCheckReminder
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
        $user_mobile = $event->user->phone;

        $check=$event->check;
        if (isset($user_mobile)) {
            $body = [
                'param1' =>verta($check->date)->format('Y/m/d'),
                'param2' => number_format($check->amount),

            ];
            SendSms::sendSingleSms('checkremindersms',$user_mobile,$body);

        }
    }
}
