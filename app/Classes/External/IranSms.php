<?php

namespace App\Classes\External;

use App\Models\SmsTemplate;
use App\Services\V1\Engin\Sms\SmsServicer;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use App\Services\V1\Auth\AdminAuthService;
class IranSms
{

    /**
     * @param string $template
     * @param $phone
     * @param array $params
     * @return void
     * @throws GuzzleException
     */
    public static function sendSingleSms($phone, string $message, ?array $connectionInfo = null, ?string $sender = null): void
    {
        $apiKey = !empty($connectionInfo['api_key']) ? $connectionInfo['api_key'] : config('services.iranSms.apikey');
        $headers = [
            'apikey' => $apiKey,
            'Content-Type' => 'application/x-www-form-urlencoded'
        ];
        $client = new Client([
            'headers' => $headers
        ]);

        $resolvedSender = $sender
            ?: (!empty($connectionInfo['line_number']) ? $connectionInfo['line_number'] : self::resolveSender());

        $body = [
            'sender' => $resolvedSender,
            'receptor' => config('app.test_status') ? config('app.test_true_sms_to') : $phone,
            'message' => $message
        ];

        $baseUrl = !empty($connectionInfo['base_url']) ? rtrim($connectionInfo['base_url'], '/') : 'http://api.parsasms.com';
        $endpoint = str_contains($baseUrl, '/sms/send') ? $baseUrl : $baseUrl . '/v2/sms/send/bulk2';

        $client->request('POST', $endpoint, [
            'form_params' => $body
        ]);
    }
    /**
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public static function fetchCredit(?array $connectionInfo = null)
    {
        $apiKey = !empty($connectionInfo['api_key']) ? $connectionInfo['api_key'] : config('services.iranSms.apikey');
        $baseUrl = !empty($connectionInfo['base_url']) ? rtrim($connectionInfo['base_url'], '/') : 'http://api.ghasedaksms.com';
        $endpoint = str_contains($baseUrl, '/credit') ? $baseUrl : $baseUrl . '/v2/credit';

        $client = new Client();
        $response = $client->request('POST', $endpoint, [
            'headers' => [
                'apikey' => $apiKey,
            ]
        ]);

        return json_decode($response->getBody()->getContents());
    }

    private static function resolveSender(): string
    {
        $ghorfe = ghorfe();

        if ($ghorfe && !empty($ghorfe->linenumber)) {
            return $ghorfe->linenumber;
        }

        return config('services.iranSms.linenumber');
    }
}
