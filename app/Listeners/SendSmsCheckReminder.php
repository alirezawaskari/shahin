<?php

namespace App\Listeners;

use App\Events\smsNotAnswer;
use App\Events\smsSuccessRegisterPayment;
use GuzzleHttp\Client;

class SendSmsCheckReminder
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
    public function handle(smsSuccessRegisterPayment $event)
    {
        $user_mobile = $event->user->phone;

        $check=$event->check;
        if (isset($user_mobile)) {
            $headers = [
                'apikey' => config('services.sms.apikey'),
                'Content-Type' => 'application/x-www-form-urlencoded'
            ];
            $client = new Client([
                'headers' => $headers
            ]);

            $body = [
                'receptor' => $user_mobile,
                'type' => 1,
                'template' => 'checkremindersms',
                'param1' =>verta($check->date)->format('Y/m/d'),
                'param2' => number_format($check->amount),

            ];
            $client->request('POST', 'http://api.parsasms.com/v2/send/verify', [
                'form_params' => $body
            ]);
        }
    }
}
