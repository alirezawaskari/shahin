<?php

declare(strict_types=1);

namespace App\Services\V1\Auth;

use App\Contracts\Auth\OtpGeneratorInterface;
use InvalidArgumentException;
use Random\RandomException;

final readonly  class NumericOtpGenerator implements OtpGeneratorInterface
{

    /**
     * @param int $length
     * @throws InvalidArgumentException
     *
     */
    public function __construct(private readonly int $length)
    {
        if ($this->length< 4 || $this->length > 9){
            throw new InvalidArgumentException(
                "OTP length must be between 4 and 9, got {$this->length}"
            );
        }
    }

    /**
     * @throws RandomException
     */
    public function generate(): string
    {
        $max = (10 ** $this->length) - 1;
        $code=random_int(0, $max);

        return str_pad((string) $code, $this->length, '0', STR_PAD_LEFT);
    }
}
