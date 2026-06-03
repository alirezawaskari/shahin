<?php


namespace App\Contracts\Auth;

use App\Exceptions\Auth\OtpNotificationException;

interface OtpGeneratorInterface
{
    /**
     * @param string $phone
     * @param string $code
     * @throws  OtpNotificationException
     */
    public function send(string $phone, string $code): void;
}
