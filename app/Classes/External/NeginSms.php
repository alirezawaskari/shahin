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
    public static function sendSingleSms(string $message, $phone,?string $portal_code = null, $service_type)
    {
        $client = new nusoap_client('https://dev.homais.com/services/sms/index.asmx?wsdl', array('encoding'=>'UTF-8'));
        $parameters['PortalCode'] = $portal_code ?? '982100044351545' ; //portal code
        $parameters['UserName'] = config('services.neginSms.username');//username
        $parameters['PassWord'] = config('services.neginSms.password'); //password
        $parameters['Mobile'] = $phone; //phone number
        $parameters['Message'] = $message;//message
        $parameters['ServerType'] = $service_type; //server type
        return $client->singleSMS($parameters)->singleSMSResult; //return result
    }


}
