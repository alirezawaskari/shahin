<?php

declare(strict_types=1);

namespace App\Repositories\Auth;


use App\Contracts\Auth\OtpRepositoryInterface;
use App\Models\AuthCode;
use Carbon\CarbonInterface;

class AuthCodeRepository implements OtpRepositoryInterface
{

    public function create(string $phone, string $code, CarbonInterface $expiresAt): AuthCode
    {
        // TODO: Implement create() method.
    }

    public function findActive(string $phone, string $code): ?AuthCode
    {
        // TODO: Implement findActive() method.
    }

    public function findMostRecentByPhone(string $phone): ?AuthCode
    {
        // TODO: Implement findMostRecentByPhone() method.
    }

    public function incrementAttempts(AuthCode $authCode): int
    {
        // TODO: Implement incrementAttempts() method.
    }

    public function markAsUsed(AuthCode $authCode): void
    {
        // TODO: Implement markAsUsed() method.
    }

    public function deleteExpired(): int
    {
        // TODO: Implement deleteExpired() method.
    }
}
