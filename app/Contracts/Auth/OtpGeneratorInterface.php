<?php

declare(strict_types=1);

namespace App\Contracts\Auth;
interface OtpGeneratorInterface
{

    /**
     * @return string
     */
    public function generate(): string;

}
