<?php

namespace App\Classes\External;

use GuzzleHttp\Client;

class IranSms
{
    /**
     * @param $phone
     * @param string $template
     * @param array $params like : ['param1'=>'example','param2'=>'',..]
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public static function send( string $template,$phone, array $params)
    {    $headers = [
        'apikey' => config('services.sms.apikey'),
        'Content-Type' => 'application/x-www-form-urlencoded'
    ];
        $client = new Client([
            'headers' => $headers
        ]);

        $body = [
            'receptor' => config('app.test_status')? config('app.test_true_sms_to'):$phone,
            'type' => 1,
            'template' => $template,
        ];
        $body=array_merge($body,$params);
        $client->request('POST', 'http://api.parsasms.com/v2/send/verify', [
            'form_params' => $body
        ]);

    }
}
