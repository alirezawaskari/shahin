<?php

namespace App\Listeners;

use App\Events\successFactor;
use GuzzleHttp\Client;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

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
                'template' => 'factorsuccess',
                'param1' => $resnumber,
            ];
            $r = $client->request('POST', 'http://api.parsasms.com/v2/send/verify', [
                'form_params' => $body
            ]);
        }
    }
}
