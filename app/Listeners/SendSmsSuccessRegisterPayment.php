<?php

namespace App\Listeners;

use App\Events\smsNotAnswer;
use App\Events\smsSuccessRegisterPayment;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class SendSmsSuccessRegisterPayment
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
        $userMobile = $event->factor->user->phone;
        $supporter_family = $event->supporter_user->family;
        $factor_resnumber = $event->factor->resnumber;
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
                'template' => 'sendsmssuccessregisterpayment',
                'param1' => $factor_resnumber,
                'param2' => $supporter_family,

            ];
            $client->request('POST', 'http://api.parsasms.com/v2/send/verify', [
                'form_params' => $body
            ]);
        }
    }
}
