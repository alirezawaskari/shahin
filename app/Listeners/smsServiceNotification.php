<?php

namespace App\Listeners;

use App\Classes\External\IranSms;
use App\Events\smsService;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Arr;

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
        $date = $event->date;

        $message = $address . '،زمان پیشنهادی شما: ' . $date;

        $body = [
            'param1' => $message,
        ];
        IranSms::sendSingleSms('customservicefactor',$userMobile,$body);


    }
}
