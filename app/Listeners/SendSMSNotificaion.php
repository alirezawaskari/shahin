<?php

namespace App\Listeners;

use App\Events\userActivation;
use App\Models\SmsLog;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

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
            $headers = [
                'apikey' => config('services.sms.apikey'),
                'Content-Type' => 'application/x-www-form-urlencoded'
            ];
            $client = new Client([
                'headers' => $headers
            ]);
            $body = [
                'receptor' => $userMobile,
                'type' => 1,
                'template' => 'fastregister',
                'param1' => $event->user->name.' '.$event->user->family,
                'param2' => $pass
            ];

            $r = $client->request('POST', 'http://api.parsasms.com/v2/send/verify', [
                'form_params' => $body
            ]);
            SmsLog::create([
                'phone'=>$userMobile,
                'type'=>'login'
            ]);
        }
    }
}
