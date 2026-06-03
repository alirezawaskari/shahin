<?php

namespace App\Services\V1\Auth;

use App\Classes\External\SendSms;
use App\Contracts\Auth\OtpGeneratorInterface;
use App\Contracts\Auth\OtpNotifierInterface;
use App\Exceptions\Auth\OtpNotificationException;

final readonly class SmsOtpNotifier implements OtpNotifierInterface
{

    /**
     * @param string $smsTemplate
     */
    public function __construct(private readonly string $smsTemplate)
    {

    }

    public function send(string $phone, string $code): void
    {
        try {
            SendSms::sendSingleSmsOtp($this->smsTemplate, $phone, ['param1' => $code]);
        } catch (\Throwable $e) {
            throw new OtpNotificationException(
                message: 'ارسال کد یکبار مصرف از طریق پیامک با شکست مواجه شد',
                previous: $e
            );
        }
    }
}
