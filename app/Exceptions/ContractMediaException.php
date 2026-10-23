<?php

namespace App\Exceptions;

use DomainException;

/**
 * A contract file action that the rules refuse (cap reached, file locked after
 * signing, wrong type or size, reject without reason). The message is the Persian text
 * shown to the user; `reason` is a stable key for tests and the UI.
 */
class ContractMediaException extends DomainException
{
    public function __construct(
        public readonly string $reason,
        string $message
    ) {
        parent::__construct($message);
    }
}
