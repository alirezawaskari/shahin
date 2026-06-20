<?php

namespace App\Classes\External;

use App\Models\Setting;
use App\Models\SmsTemplate;
use App\Services\Settings\SettingsService;
use App\Services\V1\Engin\Sms\SmsServicer;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;

class SendSms
{
    protected SettingsService $settings;

    public function __construct(SettingsService $settings)
    {
        $this->settings = $settings;
    }

    public  function sendSingleSmsTemplate(string $template, $phone, array $params)
    {
        $SmsTemplate = SmsTemplate::where('name',$template)->first();
        if($SmsTemplate){
            $message = (new SmsServicer())->generateSms($SmsTemplate->content, $params);
            if($message !== false){
                $smsService = $this->settings->get('sms_panel') ?? 'IranSms';;
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


    /**
     * Summary of sendSingleSmsOtp
     * @param string $template
     * @param mixed $phone
     * @param array $params
     * @return void
     */
    public static function sendSingleSms(string $template, $phone, array $params)
    {
        $headers = ['apikey' => config('services.iranSms.apikey'), 'Content-Type' => 'application/x-www-form-urlencoded'];
        $client = new Client(['headers' => $headers]);
        if(isset($params['param1']) && !isset($params['param2']))
        {
        $body = ['receptor' => $phone, 'type' => 1, 'template' => $template, 'param1' => $params['param1']];
        }
        if(isset($params['param2']) && !isset($params['param3']))
        {
        $body = ['receptor' => $phone, 'type' => 1, 'template' => $template, 'param1' => $params['param1'], 'param2' => $params['param2']];
        }
        if(isset($params['param3']) && !isset($params['param4']))
        {
        $body = ['receptor' => $phone, 'type' => 1, 'template' => $template, 'param1' => $params['param1'], 'param2' =>$params['param2'] , 'param3' => $params['param3']];
        }
        if(isset($params['param4']) && !isset($params['param5']))
        {
        $body = ['receptor' => $phone, 'type' => 1, 'template' => $template, 'param1' => $params['param1'], 'param2' => $params['param2'] , 'param3' => $params['param3'] , 'param4' => $params['param4']];
        }
        if(isset($params['param5']))
        {
        $body = ['receptor' => $phone, 'type' => 1, 'template' => $template, 'param1' => $params['param1'], 'param2' => $params['param2'] , 'param3' => $params['param3'] , 'param4' => $params['param4'] , 'param5' => $params['param5']];
        }
        $res = $client->request('POST', 'http://api.parsasms.com/v2/send/verify', ['form_params' => $body]);
    }
}
