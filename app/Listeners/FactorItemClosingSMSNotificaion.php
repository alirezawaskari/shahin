<?php

namespace App\Listeners;

use App\Classes\External\IranSms;
use App\Events\factorItemClosing;

class FactorItemClosingSMSNotificaion
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
     * @param factorItemClosing $event
     * @return void
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function handle(factorItemClosing $event)
    {
        $factor_item_able = $event->factorItem->factoritemable->title;
        $userMobile = $event->user->phone;
        $user = $event->user->name.' '.$event->user->family;
        if (isset($userMobile)) {
            $body = [
                'param1' => $user,
                'param2' => $factor_item_able,
            ];
            IranSms::sendSingleSms('factoritemClosing',$userMobile,$body);
        }
    }
}
