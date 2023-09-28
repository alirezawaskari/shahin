<?php

namespace App\Listeners;

use App\Classes\External\IranSms;
use App\Events\ticketNotification;
use http\Url;

class TicketSMSNotification
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
    public function handle(ticketNotification $event)
    {

        $userMobile = $event->user->phone;

        if (isset($userMobile)) {
            $name = $event->user->name .' '. $event->user->family;

            $body = [
                'param1' => $name,
                'param2'=>url('/dashboard/ticket/'.$event->ticket->id.'/edit')
            ];
            IranSms::sendSingleSms('ticketnotification',$userMobile,$body);

        }
    }
}
