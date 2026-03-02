<?php

namespace App\Listeners;

use App\Classes\External\IranSms;
use App\Classes\External\SendSms;
use App\Events\fastserviceCallcenter;

class ServiceCallcenterSMSNotification
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
     * @param fastserviceCallcenter $event
     * @return void
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function handle(fastserviceCallcenter $event)
    {
        $userMobile = $event->user;
        $service = $event->service;
        $callcenter = $event->callcenter->family;
        $type = $event->type;
        $result = self::setBodyData($event);
		$link = $result['link'];
		$organ_name = $result['organ_name'];
		$organ_call = $result['organ_call'];
        if (isset($userMobile) && $link) {
            if (!is_null($event->factor)) {
                $body = [
                    'param1' => 'services/' . $service . '/' . $event->task->user_id . '/' . $event->task->id . '/' . $event->factor->resnumber,
                    // 'param2' => $service,
                    // 'param3' => $callcenter
                ];
                SendSms::sendSingleSms('callcenterfastservice', $userMobile, $body);
            } else {
                if($type == 'sms')
                {
                    $body = [
                        'param1' => $link,
                        'param2' => $event->task->id
                    ];
                    SendSms::sendSingleSms('sendstudylinkbysms', $userMobile, $body);
                }
                else
                {
                    $body = [
                        'param1' => $organ_name,
                        'param2' => $link,
                        'param3' => $organ_call
                    ];
                    SendSms::sendSingleSms('callcenterfastservice', $userMobile, $body);
                }
            }

        }
    }

    /**
     * Summary of setBodyData
     * @param mixed $event
     * @return array|string
     */
    private function setBodyData($event)
    {
        $link = "";
		$organ_name = "شبکه خدمات رفاهی لاله";
		$organ_call = "02191095745";
        if($event->service->id == 104)
            $link = 'centerCampaign';
        if($event->service->id == 197)
            $link = 'Permanent-employment';
        $single_web_page = $event->service->singleWebPages->first();
        $link = 'services/' . $event->service->slug . '/' . $event->task->user_id . '/' . $event->task->id;
        if (auth()->user() && auth()->user()->admin_representation) {
            $link = auth()->user()->admin_representation->slug . '/' . $event->service->slug;
			$organ_name = auth()->user()->admin_representation->fa_name;
			$organ_call = auth()->user()->admin_representation->call;
        }
        if ($single_web_page) {
            $link = 'singleWebPage/' . $single_web_page->slug . '/' . $event->service->slug;
		    $organ_name = $single_web_page->fa_name;
        }
		$result['link']=$link;
        $result['organ_name']=$organ_name;
		$result['organ_call']=$organ_call;

        return $result;
    }
}
