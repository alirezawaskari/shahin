<?php

namespace App\Listeners;

use App\Events\factorItemActivation;
use GuzzleHttp\Client;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class FactorItemActivationSMSNotificaion
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
     * @param factorItemActivation $event
     * @return void
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function handle(factorItemActivation $event)
    {

        $userMobile = $event->user->phone;
        $center = $event->center;

        if (isset($userMobile)) {
            $headers = [
                'apikey' => config('services.sms.apikey'),
                'Content-Type' => 'application/x-www-form-urlencoded'
            ];
            $client = new Client([
                'headers' => $headers
            ]);
            $name = $event->user->name .' '. $event->user->family;

            $body = [
                'receptor' => $userMobile,
                'type' => 1,
                'template' => 'factoritemactivation',
                'param1' => $name,
                'param2' => $center
            ];
            $r = $client->request('POST', 'http://api.parsasms.com/v2/send/verify', [
                'form_params' => $body
            ]);
        }
    }
}
