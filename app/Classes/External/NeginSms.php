<?php

namespace App\Classes\External;

use App\Models\SmsTemplate;
use App\Services\V1\Engin\Sms\SmsServicer;
use nusoap_client;


class NeginSms
{
    /**
     * @param string $message
     * @param $phone
     * @param string|null $portal_code
     * @param $service_type
     * @return mixed
     */
    public static function sendSingleSms(string $message, $phone, $service_type = 'single', ?array $connectionInfo = null)
    {
        $wsdl = !empty($connectionInfo['base_url']) ? $connectionInfo['base_url'] : 'https://dev.homais.com/services/sms/index.asmx?wsdl';
        $client = new nusoap_client($wsdl, array('encoding'=>'UTF-8'));

        $portalCode = !empty($connectionInfo['portal_code'])
            ? $connectionInfo['portal_code']
            : (!empty($connectionInfo['portalcode']) ? $connectionInfo['portalcode'] : config('services.neginSms.portalcode'));
        $username = !empty($connectionInfo['username']) ? $connectionInfo['username'] : config('services.neginSms.username');
        $password = !empty($connectionInfo['password']) ? $connectionInfo['password'] : config('services.neginSms.password');

        $parameters['PortalCode'] = $portalCode;
        $parameters['UserName'] = $username;
        $parameters['PassWord'] = $password;
        $parameters['Mobile'] = $phone;
        $parameters['Message'] = $message;
        $parameters['ServerType'] = $service_type;
        return $client->singleSMS($parameters)->singleSMSResult;
    }


}
