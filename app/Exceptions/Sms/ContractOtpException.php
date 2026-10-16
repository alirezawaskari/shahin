<?php

namespace App\Exceptions\Sms;

use DomainException;

/**
 * Why a contract-signing OTP could not be issued or accepted. `reason` is a stable machine
 * key for the UI/tests; the message is the Persian text shown to the signer.
 */
class ContractOtpException extends DomainException
{
    public function __construct(
        public readonly string $reason,
        string $message,
        public readonly int $retryAfter = 0
    ) {
        parent::__construct($message);
    }
}
