<?php

declare(strict_types=1);

namespace App\Contracts\Auth;

use App\Models\AuthCode;
use Carbon\CarbonInterface;


interface OtpRepositoryInterface
{
    public function create(string $phone, string $code, CarbonInterface $expiresAt): AuthCode;


    public function findActive(string $phone, string $code): ?AuthCode;


    public function findMostRecentByPhone(string $phone): ?AuthCode;


    public function incrementAttempts(AuthCode $authCode): int;

    public function markAsUsed(AuthCode $authCode): void;

    public function deleteExpired(): int;
}
