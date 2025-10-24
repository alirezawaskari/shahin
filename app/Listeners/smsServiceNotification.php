<?php

namespace App\Listeners;

use App\Classes\External\IranSms;
use App\Classes\External\SendSms;
use App\Events\smsService;
use GuzzleHttp\Exception\GuzzleException;

class smsServiceNotification
{
    /**
     * Handle the event.
     *
     * @param smsService $event
     * @return void
     * @throws GuzzleException
     */
    public function handle(smsService $event)
    {
        $userMobile = $event->user->phone;
        $address = $event->address;
        $date = verta($event->date)->format('Y/m/d H:i:s');

        $message = $address . '،زمان پیشنهادی شما: ' . $date;

        $body = [
            'param1' => $message,
        ];
        SendSms::sendSingleSms('customservicefactor',$userMobile,$body);


    }
}
