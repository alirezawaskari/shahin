<?php

namespace App\Listeners;

use App\Classes\External\IranSms;
use App\Classes\External\SendSms;
use App\Events\smsNotAnswer;
use Carbon\Carbon;

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
        //$callcenter = $event->callcenter->family;
        $callcenter = auth()->user()->family;
        if (isset($userMobile)) {
            $body = [
                'param1' => verta(Carbon::now())->format('Y/m/d H:i:s'),
                'param2' => $event->unit,
                'param3' => auth()->user()->phone,
                'param4' => $callcenter,
            ];
            SendSms::sendSingleSms('sendsmsnotanswer',$userMobile,$body);

        }
    }
}
