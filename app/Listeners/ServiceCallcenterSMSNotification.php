<?php

namespace App\Listeners;

use App\Classes\External\IranSms;
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
        $link = self::createLink($event);
        if (isset($userMobile) && $link) {
            if (!is_null($event->factor)) {
                $body = [
                    'param1' => 'services/' . $service . '/' . $event->task->user_id . '/' . $event->task->id . '/' . $event->factor->resnumber,
                    'param2' => $service,
                    'param3' => $callcenter
                ];
                IranSms::sendSingleSms('callcenterfastservice', $userMobile, $body);
            } else {
                if($type == 'sms')
                {
                    $body = [
                        'param1' => $link,
                        'param2' => $event->task->id
                    ];
                    IranSms::sendSingleSms('sendstudylinkbysms', $userMobile, $body);
                }
                else
                {
                    $body = [
                        'param1' => $link,
                        'param2' => $service->slug,
                        'param3' => $callcenter
                    ];
                    IranSms::sendSingleSms('callcenterfastservice', $userMobile, $body);
                }
            }

        }
    }

    private function createLink($event)
    {
        if($event->service->id == 104)
            return 'centerCampaign';
        $link = "";
        $single_web_page = $event->service->singleWebPages->first();
        $link = 'services/' . $event->service->slug . '/' . $event->task->user_id . '/' . $event->task->id;
        if (auth()->user() && auth()->user()->admin_representation) {
            $link = '/' . auth()->user()->admin_representation->slug . '/' . $event->service->slug;
        }
        if ($single_web_page) {
            $link = 'singleWebPage/' . $single_web_page->slug . '/' . $event->service->slug;
        }
        return $link;
    }
}
