<?php

namespace App\Listeners;

use App\Events\fastregisterorganCallcenter;
use GuzzleHttp\Client;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class RegOrganSMSNotification
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
     * @param fastregisterorganCallcenter $event
     * @return void
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function handle(fastregisterorganCallcenter $event)
    {
        $userMobile = $event->user->phone;
        $pass = $event->pass;
        $userWallet = $event->user->wallet;

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
                'template' => 'organizationfastregister',
                'param1' => $userMobile,
                'param2' => $pass,
                'param3' => $userWallet,
            ];
            $r = $client->request('POST', 'http://api.parsasms.com/v2/send/verify', [
                'form_params' => $body
            ]);
        }
    }
}
