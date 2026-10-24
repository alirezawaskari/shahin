<?php

namespace App\Exceptions\Sms;

use DomainException;

/**
 * A business's own SMS panel (OTP or advertising) was disabled by staff. Thrown before any message,
 * recipient or charge is created. Reports and logs are not affected.
 */
class SmsPanelDisabledException extends DomainException
{
    public function __construct(public readonly string $panelType, string $message)
    {
        parent::__construct($message);
    }
}
