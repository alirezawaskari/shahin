<?php

namespace App\Listeners;

use App\Classes\External\IranSms;
use App\Classes\External\SendSms;
use App\Events\userActivation;
use App\Models\SmsLog;
use Carbon\Carbon;

class SendSMSNotificaion
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
     * @param userActivation $event
     * @return false|void
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function handle(userActivation $event)
    {
        $userMobile = $event->user->phone;

        if(SmsLog::where('phone',$userMobile)->whereDate('created_at', Carbon::today())->count() >9
            || !is_null(SmsLog::where('phone',$userMobile)->where('created_at','>', Carbon::now()->subSeconds(120))->first()) ){
            return false;
        }
        $pass = $event->pass;

        if (isset($userMobile)) {
            $body = [
                'param1' => $event->user->name.' '.$event->user->family,
                'param2' => $pass
            ];
            SendSms::sendSingleSms('fastregister',$userMobile,$body);
            SmsLog::create([
                'phone'=>$userMobile,
                'type'=>'login'
            ]);

        }
    }
}
