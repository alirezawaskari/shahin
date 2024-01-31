<?php

namespace App\Listeners;

use App\Classes\External\IranSms;
use App\Events\smsNotAnswer;
use App\Events\smsSuccessRegisterPayment;
use App\Models\Factor;

class SendSmsSuccessRegisterPayment
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
    public function handle(smsSuccessRegisterPayment $event)
    {
        $userMobile = $event->factor->user->phone;
        if (isset($userMobile)) {
            $body = [
                'param1' => self::getUploadDocumentLink($event->factor->resnumber),
                'param2' => $event->supporter_user->family,
                'param3' => $event->supporter_user->phone,

            ];
            IranSms::sendSingleSms('sendsmssuccessregisterpayment',$userMobile,$body);

        }
    }
    private function getUploadDocumentLink($resnumber)
    {
        $link = 'lalecard.ir/pf/'.$resnumber;
        return $link;
    }
}
