<?php

declare(strict_types=1);

namespace App\Exceptions\Auth;

use Exception;

abstract class AdminAuthException extends Exception
{
    abstract public function statusCode(): int;

    abstract public function userMessage(): string;

}
