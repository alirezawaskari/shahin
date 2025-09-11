<?php

namespace App\Classes\External;

use App\Models\SmsTemplate;
use App\Services\V1\Engin\Sms\SmsServicer;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class IranSms
{
    /**
     * @param string $template
     * @param $phone
     * @param array $params
     * @return void
     * @throws GuzzleException
     */
    public static function sendSingleSms($phone , string $message): void
    {
        $headers = [
            'apikey' => config('services.iranSms.apikey'),
            'Content-Type' => 'application/x-www-form-urlencoded'
        ];
        $client = new Client( [
            'headers' => $headers
        ] );
        $body = [
            //30005006003675
            'sender' => config( 'services.iranSms.linenumber' ),
            'receptor' => config('app.test_status')? config('app.test_true_sms_to'):$phone,
            'message' => $message
        ];
        $client->request( 'POST', 'http://api.parsasms.com/v2/sms/send/bulk2', [
            'form_params' => $body
        ] );
    }
    /**
     * @return string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public static function fetchCredit()
    {
        $client = new Client();
        $response = $client->request('POST', 'http://api.ghasedaksms.com/v2/credit', [
            'headers' => [
                'apikey' =>config('services.iranSms.apikey'),
            ]
        ]);

        return json_decode($response->getBody()->getContents());
    }

}
