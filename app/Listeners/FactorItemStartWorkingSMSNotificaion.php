<?php

namespace App\Listeners;

use App\Events\factorItemStartWorking;
use GuzzleHttp\Client;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class FactorItemStartWorkingSMSNotificaion
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
     * @param factorItemStart $event
     * @return void
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function handle(factorItemStartWorking $event)
    {
        $factor_item_able = $event->factorItem->factoritemable->title;
        $resnumber = $event->factorItem->resnumber;
        $userMobile = $event->user->phone;
        if (isset($userMobile)) {
            $headers = [
                'apikey' => config('services.sms.apikey'),
                'Content-Type' => 'application/x-www-form-urlencoded'
            ];
            $client = new Client([
                'headers' => $headers
            ]);

            $access = 'شماره موبایل : ' . $event->user->phone .' و رمز عبور :'. $event->pass;

            $body = [
                'receptor' => $userMobile,
                'type' => 1,
                'template' => 'factoritemstartworking',
                'param1' => $factor_item_able,
                'param2' => $access,
                'param3' => 'lalecard.ir/factorItem/'.$resnumber,
            ];

            $r = $client->request('POST', 'http://api.parsasms.com/v2/send/verify', [
                'form_params' => $body
            ]);
        }
    }
}
