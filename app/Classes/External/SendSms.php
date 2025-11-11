<?php

namespace App\Classes\External;

use App\Models\Setting;
use App\Models\sms;
use App\Models\SmsTemplate;
use App\Services\V1\Engin\Sms\SmsServicer;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;

class SendSms
{
    /**
     * @throws GuzzleException
     */
    public static function sendSingleSms(string $template, $phone, array $params)
    {
        $SmsTemplate = SmsTemplate::where('name',$template)->first();
        if($SmsTemplate){
            $message = (new SmsServicer())->generateSms($SmsTemplate->content, $params);
            if($message !== false){
                $smsService = Setting::first()->sms_panel ?? 'IranSms';
                if ($smsService === 'NeginSms') {
                    NeginSms::sendSingleSms($phone, $message,service_type:'test');
                } else {
                    IranSms::sendSingleSms($phone, $message);
                }
            }else{
                return redirect('500');
            }
        }else{
           return redirect('500');
        }
    }
}
