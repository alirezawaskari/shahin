<?php

namespace App\Listeners;

use App\Classes\External\IranSms;
use App\Events\smsServiceSession;
use GuzzleHttp\Exception\GuzzleException;

class smsServiceSessionNotification
{
    /**
     * Handle the event.
     *
     * @param smsService $event
     * @return void
     * @throws GuzzleException
     */
    public function handle(smsServiceSession $event)
    {
        $userMobile = $event->factor_item->factor->user->phone;
//        $resnumber = $event->factor_item->resnumber;
        $service = $event->factor_item->factoritemable->title;
        $date = $event->date;


        $body = [
            'param1' => $service,
            'param2' => $date,
        ];

        IranSms::sendSingleSms('dateservicesession',$userMobile,$body);

    }
}
