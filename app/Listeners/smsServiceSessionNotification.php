<?php

namespace App\Listeners;

use App\Events\smsServiceSession;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Arr;

class smsServiceSessionNotification
{
    /**
     * Handle the event.
     *
     * @param smsService $event
     * @return void
     * @throws GuzzleException
     */
    public function handle(smsServiceSession $event)
    {
        $userMobile = $event->factor_item->factor->user->phone;
        $resnumber = $event->factor_item->resnumber;
        $service = $event->factor_item->factoritemable->title;
        $date = $event->date;

        $headers = [
            'apikey' => config('services.sms.apikey'),
            'Content-Type' => 'application/x-www-form-urlencoded'
        ];
        $client = new Client([
            'headers' => $headers
        ]);

        $body = [
            'receptor' => $userMobile,
            'type' => 1,
            'template' => 'dateservicesession',
            'param1' => $service,
            'param2' => $date,
        ];

        $client->request('POST', 'http://api.parsasms.com/v2/send/verify', [
            'form_params' => $body
        ]);
    }
}
