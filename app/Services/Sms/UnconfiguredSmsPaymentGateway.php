<?php

namespace App\Services\Sms;

use App\Exceptions\Sms\SmsPaymentUnavailableException;
use App\Models\SmsTopUpPayment;

/**
 * Fail-closed default: the payment module has no generic "pay for a custom
 * purpose" entry point yet, so until an adapter is bound no balance can be
 * bought (and none is ever credited without a payment).
 */
class UnconfiguredSmsPaymentGateway implements SmsPaymentGateway
{
    public function start(SmsTopUpPayment $payment): string
    {
        throw new SmsPaymentUnavailableException('درگاه پرداخت برای شارژ پیامک هنوز متصل نشده است. لطفاً با پشتیبانی تماس بگیرید.');
    }
}
