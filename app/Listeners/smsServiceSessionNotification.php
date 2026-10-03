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
        $user = $event->factorItem->factor->user->name.' '.$event->factorItem->factor->user->family;
        $factorItem = $event->factorItem;
        $service = $event->factorItem->factoritemable->title;
        $center_title = $factorItem->center->title;
        $center_call = $factorItem->center->phone;
        $center_address = $factorItem->center->address;
        $date = $event->date;
        $supporter_name = $factorItem->supporter ? $factorItem->supporter->family : '';

        $body = [
            'param1' => $user,
            'param2' => $service,
            'param3' => $center_title,
            'param4' => $date,
            'param5' => $supporter_name,
            'param6' => $center_address,
            'param7' => $center_call,
        ];

        SendSms::sendSingleSms('timeWorkExecutionNotification',$userMobile,$body);

    }
}
