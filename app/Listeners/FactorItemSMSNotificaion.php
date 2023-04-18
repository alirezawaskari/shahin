<?php

namespace App\Listeners;

use App\Classes\External\IranSms;
use App\Events\factorItemActivation;


class FactorItemSMSNotificaion
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
     * @param factorItemActivation $event
     * @return void
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function handle(factorItemActivation $event)
    {

        $userMobile = $event->user->phone;
        $center = $event->center;

        if (isset($userMobile)) {
            $name = $event->user->name .' '. $event->user->family;
            IranSms::sendSingleSms('factoritemactivation',$userMobile,array('param1' => $name, 'param2' => $center));

        }
    }
}
