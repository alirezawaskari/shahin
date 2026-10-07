<?php

namespace App\Exceptions\Sms;

use DomainException;

class TierAccessDeniedException extends DomainException
{
    public function __construct(
        string $message,
        public readonly string $requiredTier,
        public readonly string $userTier
    ) {
        parent::__construct($message);
    }
}
