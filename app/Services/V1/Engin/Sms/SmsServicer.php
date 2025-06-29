<?php

namespace App\Services\V1\Engin\Sms;

class SmsServicer
{
    public function generateSms(string $template, array $params): false|string
    {
        preg_match_all('/%[^%]+%/', $template, $matches);
        $search = $matches[0];
        $replace = array_values($params);
        if (count($search) !== count($replace)) {
            return false;
        }
        return str_replace($search, $replace, $template);
    }
}
