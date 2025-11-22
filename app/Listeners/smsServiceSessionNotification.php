<?php

namespace App\Listeners;

use App\Classes\External\IranSms;
use App\Classes\External\SendSms;
use App\Events\smsServiceSession;
use GuzzleHttp\Exception\GuzzleException;

class smsServiceSessionNotification
{
    /**
     * Handle the event.
     *
     * @param smsServiceSession $event
     * @return void
     * @throws GuzzleException
     */
    public function handle(smsServiceSession $event)
    {
        $userMobile = $event->factorItem->factor->user->phone;
        $factorItem = $event->factorItem->factor->user->phone;
        $service = $event->factorItem->factoritemable->title;
        $center_title = $factorItem->center->title;
        $center_call = $factorItem->center->phone;
        $center_address = $factorItem->center->address;
        $date = $event->date;
        $body = [
            'param1' => $service,
            'param2' => $center_title,
            'param3' => $date,
            'param4' => $center_address,
            'param5' => $center_call,
        ];

        SendSms::sendSingleSms('timeWorkExecutionNotification',$userMobile,$body);

    }
}
