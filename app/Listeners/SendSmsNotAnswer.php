<?php

namespace App\Listeners;

use App\Classes\External\IranSms;
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
        $callcenter = $event->callcenter->family;

        if (isset($userMobile)) {
            $body = [
                'param1' => verta(Carbon::now())->format('Y/m/d H:i:s'),
                'param2' => $callcenter,
                'param3' => $event->unit,
            ];
            IranSms::sendSingleSms('sendsmsnotanswer',$userMobile,$body);

        }
    }
}
