<?php

namespace App\Classes\External;

use App\Models\sms;
use App\Models\SmsTemplate;
use App\Services\V1\Engin\Sms\SmsServicer;
class SendSms
{
    public static function sendSingleSms(string $template, $phone, array $params): void
    {
        $SmsTemplate = SmsTemplate::where('name',$template)->first()->content;
        $message = (new SmsServicer())->generateSms($SmsTemplate, $params);
        dd($message);
        $smsService = config('services.sms.name','IranSms');
        if ($smsService === 'NeginSms') {
            NeginSms::sendSingleSms($phone, $message,null,service_type:'test');
        } else {
            IranSms::sendSingleSms($phone, $message);
        }
    }
}
