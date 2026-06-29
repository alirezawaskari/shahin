<?php

namespace App\Listeners;

use App\Classes\External\IranSms;
use App\Classes\External\SendSms;
use App\Events\fastserviceCallcenter;

class ServiceCallcenterSMSNotification {
    /**
    * Create the event listener.
    *
    * @return void
    */

    public function __construct() {
        //
    }

    /**
    * Handle the event.
    *
    * @param fastserviceCallcenter $event
    * @return void
    * @throws \GuzzleHttp\Exception\GuzzleException
    */

    public function handle( fastserviceCallcenter $event ) {
        $user = $event->user;
        $userMobile = $user->phone;
        $service = $event->service;
        $callcenter = $event->callcenter->family;
        $type = $event->type;
        $result = self::setBodyData( $event );
        $link = $result[ 'link' ];
        $organ_name = $result[ 'organ_name' ];
        $organ_call = $result[ 'organ_call' ];
        if ( isset( $userMobile ) && $link ) {
            if ( !is_null( $event->factor ) ) {
                $body = [
                    'param1' => 'services/' . $service . '/' . $event->task->user_id . '/' . $event->task->id . '/' . $event->factor->resnumber,
                    // 'param2' => $service,
                    // 'param3' => $callcenter
                ];
                SendSms::sendSingleSms( 'callcenterfastservice', $userMobile, $body );
            } else {
                if ( $type == 'sms' ) {
                    $body = [
                        'param1' => $link,
                        'param2' => $event->task->id
                    ];
                    SendSms::sendSingleSms( 'sendstudylinkbysms', $userMobile, $body );
                } else {
                    $body = [
                        'param1' => $user->name.' '.$user->family,
                        'param2' => $organ_name,
                        'param3' => $callcenter,
                        'param4' => $link,
                        'param5' => $organ_call
                    ];
                    SendSms::sendSingleSms( 'callcenterfastservice', $userMobile, $body );
                }
            }

        }
    }

    /**
    * Summary of setBodyData
    * @param mixed $event
    * @return array|string
    */

    private function setBodyData( $event ): array {
        $link = '';
        $organName = 'شبکه خدمات رفاهی لاله';
        $organCall = '02191095745';

        $service = $event->service;
        $task = $event->task;
        $type = $event->type;

        $link = match ( $service->id ) {
            104 => 'centerRequest/create',
            197 => 'Permanent-employment',
            default => "services/{$service->slug}/{$task->user_id}/{$task->id}",
        };

        $singleWebPage = $service->singleWebPages ? $service->singleWebPages->first() : null;

        if ( $singleWebPage ) {
            $link = "singleWebPage/{$singleWebPage->slug}/{$service->slug}";
            $organName = $singleWebPage->fa_name;
            $organCall = $singleWebPage->call;
        }

        if ( $adminRep = optional( auth()->user() )->admin_representation ) {
            $link = $type === 'serviceItem'
            ? "{$adminRep->slug}/serviceItem/{$service->slug}"
            : "{$adminRep->slug}/{$service->slug}";

            $organName = $adminRep->fa_name;
            $organCall = $adminRep->call;
        }

        return [
            'link'       => $link,
            'organ_name' => $organName,
            'organ_call'  => $organCall,
        ];
    }
}
