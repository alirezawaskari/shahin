<?php

namespace App\Exceptions\Sms;

use DomainException;

class InsufficientBalanceException extends DomainException
{
    public function __construct(
        string $message = 'موجودی حساب برای ارسال این پیامک کافی نیست.',
        public readonly float $currentBalance = 0,
        public readonly float $requiredAmount = 0
    ) {
        parent::__construct($message);
    }
}
