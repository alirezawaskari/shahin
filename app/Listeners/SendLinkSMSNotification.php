<?php

namespace App\Listeners;

use App\Classes\External\IranSms;
use App\Classes\External\SendSms;
use App\Events\sendlinkFactor;
use App\Models\Setting;
use App\Services\Settings\SettingsService;

class SendLinkSMSNotification {
    /**
    * Create the event listener.
    *
    * @return void
    */

    protected $settings;

    public function __construct(SettingsService $settings)
    {
        $this->settings = $settings;
    }


    /**
     * Summary of handle
     * @param sendlinkFactor $event
     * @return void
     */
    public function handle( sendlinkFactor $event )
	{
        $userMobile = $event->user->phone;
        $factor = $event->factor->resnumber;
        $userName = $event->user->name.' '.$event->user->family;
        $callcenter_phone = $event->factor->task && isset($event->factor->task->callcenter) ? $event->factor->task->callcenter->employee->business_phone : '';

        if ( isset( $userMobile ) ) {
            if ( $event->type == 'online' ) {
                $body = [
                    'param1' => $factor,
                ];
                SendSms::sendSingleSms('sendlinkfactor', $userMobile, $body);
            } elseif ( $event->type == 'send_card_number' ) {

                $body = [
                    'param1' => $userName,
                    'param2' => number_format( $event->factor->price ),
                    'param3' =>$this->settings->get('card_number'),
                    'param4' =>$this->settings->get('shaba_number'),
                    'param5' =>$this->settings->get('card_holder'),
                    'param6' => $factor,
                    'param7' => $callcenter_phone,
                ];
                SendSms::sendSingleSms('sendcardnumber', $userMobile, $body);
            } elseif( $event->type == 'tax_free_card')
            {
                $body = [
                    'param1' => $userName,
                    'param2' => number_format( $event->factor->price ),
                    'param3' =>$this->settings->get('tax_free_card_number'),
                    'param4' =>$this->settings->get('tax_free_shaba_number'),
                    'param5' =>$this->settings->get('tax_free_card_holder'),
                    'param6' => $factor,
                    'param7' => $callcenter_phone,
                ];
                SendSms::sendSingleSms('sendcardnumber', $userMobile, $body);
            }
        }
    }
}
