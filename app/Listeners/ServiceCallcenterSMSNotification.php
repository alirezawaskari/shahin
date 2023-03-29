<?php

namespace App\Listeners;

use App\Events\fastserviceCallcenter;
use GuzzleHttp\Client;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

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
        $userMobile = $event->user->phone;
        $service = $event->service;
        $callcenter = $event->callcenter->family;
        $type = $event->type;
        $link = self::createLink($event);

        if (isset($userMobile) && $link) {
            $headers = [
                'apikey' => config('services.sms.apikey'),
                'Content-Type' => 'application/x-www-form-urlencoded'
            ];
            $client = new Client([
                'headers' => $headers
            ]);
            if ($type == "card"){
                $body = [
                    'receptor' => $userMobile,
                    'type' => 1,
                    'template' => 'callcenterfastservice',
                    'param1' => $event->factor->resnumber,
                    'param2' => 'شماره کارت 5047061090019510 به نام شرکت جانبازان آینده ساز' . '- مبلغ: ' . number_format($price) . ' ریال',
                    'param3' => $callcenter
                ];
            }else{
                if(!is_null($event->factor)){
                $body = [
                    'receptor' => $userMobile,
                    'type' => 1,
                    'template' => 'callcenterfastservice',
                    'param1' => 'services/'.$service.'/'.$event->task->user_id.'/'.$event->task->id.'/'.$event->factor->resnumber,
					'param2' => $service,
                    'param3' => $callcenter
                ];
                }else{
                    $body = [
                        'receptor' => $userMobile,
                        'type' => 1,
                        'template' => 'callcenterfastservice',
                        'param1' => $link,
                        'param2' => $service->slug,
                        'param3' => $callcenter
                    ];
                }
            }

              $client->request('POST', 'http://api.parsasms.com/v2/send/verify', [
                'form_params' => $body
            ]);
        }
    }

    private function createLink($event)
    {
        $link = "";
        $representation = $event->service->representations->first();
        if($event->service->isShow)
        {
            $link = 'services/'.$event->service->slug.'/'.$event->task->user_id.'/'.$event->task->id;
        }
        elseif($representation)
        {
            $link = $representation->slug.'/'.$event->service->slug;
        }

        return $link;
    }
}
