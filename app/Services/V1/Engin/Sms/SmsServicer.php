<?php

namespace App\Services\V1\Engin\Sms;

class SmsServicer
{
    public function generateSms($template,array $params): false|string
    {
        preg_match_all('/%param\d+%/', $template, $matches);
        $count = count(array_unique($matches[0]));
        if($count === count($params)){
            return strtr($template, $params);
        }
        return false;
    }
}
