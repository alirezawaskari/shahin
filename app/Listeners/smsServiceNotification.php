<?php

namespace App\Listeners;

use App\Classes\External\IranSms;
use App\Classes\External\SendSms;
use App\Events\smsService;
use App\Models\FactorItem;
use GuzzleHttp\Exception\GuzzleException;

class smsServiceNotification
{
    /**
     * Handle the event.
     *
     * @param smsService $event
     * @return void
     * @throws GuzzleException
     */
    public function handle(smsService $event)
    {
        $factorItem = $event->factorItem;
        $userMobile = $factorItem->factor->user->phone;
        $center_title = $factorItem->center->title;
        $center_call = $factorItem->center->call;
        $service = FactorItem::getFactorItemAble($factorItem , []);
        $body = [
            'param1' => $service,
            'param2' => $center_title,
            'param3' => $center_call,
        ];
        SendSms::sendSingleSms('customservicefactor',$userMobile,$body);
    }
}
