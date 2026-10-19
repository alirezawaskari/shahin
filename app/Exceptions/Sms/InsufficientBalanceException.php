<?php

namespace App\Exceptions\Sms;

use App\Models\SenderNumber;
use DomainException;

class InsufficientBalanceException extends DomainException
{
    public function __construct(
        string $message = 'موجودی حساب برای ارسال این پیامک کافی نیست.',
        public readonly float $currentBalance = 0,
        public readonly float $requiredAmount = 0,
        public readonly ?SenderNumber $alternativeSender = null,
        // Bulk flow only: how many recipients the current balance can still pay for.
        public readonly ?int $maxAffordableRecipients = null
    ) {
        parent::__construct($message);
    }
}
