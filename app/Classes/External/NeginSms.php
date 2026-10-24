<?php

namespace App\Classes\External;

use nusoap_client;

class NeginSms
{
    /**
     * Send single SMS via NeginSms (Homa System) SOAP API.
     *
     * @param string $message
     * @param string $phone
     * @param int|string $service_type
     * @param array|null $connectionInfo
     * @return mixed
     */
    public static function sendSingleSms($message, $phone, $service_type = 0, ?array $connectionInfo = null)
    {
        // Support either parameter order ($message, $phone) or ($phone, $message)
        if (preg_match('/^(\+98|0)?9\d{9}$/', (string) $message) && !preg_match('/^(\+98|0)?9\d{9}$/', (string) $phone)) {
            $tmp = $message;
            $message = $phone;
            $phone = $tmp;
        }

        $wsdl = !empty($connectionInfo['base_url']) ? $connectionInfo['base_url'] : 'https://dev.homais.com/services/sms/index.asmx?wsdl';
        $client = new nusoap_client($wsdl, 'wsdl');
        $client->soap_defencoding = 'UTF-8';
        $client->decode_utf8 = false;

        $portalCode = !empty($connectionInfo['portal_code'])
            ? $connectionInfo['portal_code']
            : (!empty($connectionInfo['portalcode']) ? $connectionInfo['portalcode'] : config('services.neginSms.portalcode'));
        $username = !empty($connectionInfo['username']) ? $connectionInfo['username'] : config('services.neginSms.username');
        $password = !empty($connectionInfo['password']) ? $connectionInfo['password'] : config('services.neginSms.password');

        $serverType = is_numeric($service_type) ? (int) $service_type : 0;

        $parameters = [
            'PortalCode' => (int) $portalCode,
            'UserName' => (string) $username,
            'PassWord' => (string) $password,
            'Mobile' => (string) $phone,
            'Message' => (string) $message,
            'ServerType' => $serverType,
        ];

        $result = $client->call('singleSMS', $parameters);

        if ($client->fault) {
            $faultStr = is_array($result) ? ($result['faultstring'] ?? json_encode($result, JSON_UNESCAPED_UNICODE)) : (string) $result;
            throw new \RuntimeException('NeginSms SOAP Fault: ' . $faultStr);
        }

        if ($err = $client->getError()) {
            throw new \RuntimeException('NeginSms SOAP Error: ' . $err);
        }

        if (is_array($result) && isset($result['singleSMSResult'])) {
            return $result['singleSMSResult'];
        }

        return $result;
    }
}
