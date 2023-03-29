<?php

namespace App\Listeners;

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

        $headers = [
            'apikey' => config('services.sms.apikey'),
            'Content-Type' => 'application/x-www-form-urlencoded'
        ];
        $client = new Client([
            'headers' => $headers
        ]);

        $message = $address . '،زمان پیشنهادی شما: ' . $date;

        $body = [
            'receptor' => $userMobile,
            'type' => 1,
            'template' => 'customservicefactor',
            'param1' => $message,
        ];

        $client->request('POST', 'http://api.parsasms.com/v2/send/verify', [
            'form_params' => $body
        ]);
    }
}
