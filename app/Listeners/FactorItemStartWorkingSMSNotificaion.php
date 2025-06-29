<?php

namespace App\Listeners;

use App\Classes\External\IranSms;
use App\Classes\External\SendSms;
use App\Events\factorItemStartWorking;

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

            $access = 'شماره موبایل : ' . $event->user->phone .' و رمز عبور :'. $event->pass;
            $body = [
                'param1' => $factor_item_able,
                'param2' => $access,
                'param3' => 'lalecard.ir/factorItem/'.$resnumber,
            ];
            SendSms::sendSingleSms('factoritemstartworking',$userMobile,$body);


        }
    }
}
