<?php

namespace App\Listeners;

use App\Events\fastregisterCallcenter;
use GuzzleHttp\Client;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

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
                'template' => 'callcenterfastregister',
                'param1' => $userMobile,
                'param2' => $pass,
            ];
            $r = $client->request('POST', 'http://api.parsasms.com/v2/send/verify', [
                'form_params' => $body
            ]);
        }
    }
}
