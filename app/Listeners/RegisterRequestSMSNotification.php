<?php

namespace App\Listeners;

use App\Classes\External\IranSms;
use App\Classes\External\SendSms;
use App\Events\registerRequestNotification;

class RegisterRequestSMSNotification
{
    /**
     * Handle the event.
     *
     * @param factorItemActivation $event
     * @return void
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function handle(registerRequestNotification $event)
    {
        $user = $event->user;

        if (isset($user->phone)) {
            $name = $user->name .' '. $user->family;

            $body = [
                'param1' => $name,
            ];
            SendSms::sendSingleSms('registerRequestNotification',$user->phone,$body);
        }
    }
}
