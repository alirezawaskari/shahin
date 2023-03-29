<?php

namespace App\Listeners;

use App\Events\smsNotAnswer;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendSmsNotAnswer
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
    public function handle(smsNotAnswer $event)
    {
        $userMobile = $event->user->phone;
        $callcenter = $event->callcenter->family;

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
                'template' => 'sendsmsnotanswer',
                'param1' => verta(Carbon::now())->format('Y/m/d H:i:s'),
                'param2' => $callcenter,
                'param3' => $event->unit,
            ];
            $r = $client->request('POST', 'http://api.parsasms.com/v2/send/verify', [
                'form_params' => $body
            ]);
        }
    }
}
