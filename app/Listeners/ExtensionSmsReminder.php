<?php

namespace App\Listeners;

use App\Classes\External\IranSms;
use App\Events\ExtensionDate;
use App\Events\smsNotAnswer;
 use GuzzleHttp\Client;

class ExtensionSmsReminder
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
    public function handle(ExtensionDate $event)
    {
            IranSms::sendSingleSms('extensionsmsreminder',$event->phone,array('param1'=>$event->item));
    }
}
