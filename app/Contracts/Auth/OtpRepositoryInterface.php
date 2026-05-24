<?php

declare(strict_types=1);

namespace App\Contracts\Auth;

use App\Models\AuthCode;
use Carbon\CarbonInterface;


interface OtpRepositoryInterface
{
    /**
     * ساخت رکورد جدید برای کد یکبار مصرف.
     *
     * @param  string                 $phone      شماره موبایل ادمین
     * @param  string                 $code       کد تولید شده
     * @param  CarbonInterface        $expiresAt  زمان انقضای کد
     * @return AuthCode                            رکورد ساخته شده
     */
    public function create(string $phone, string $code, CarbonInterface $expiresAt): AuthCode;


    public function findActive(string $phone, string $code): ?AuthCode;


    public function findMostRecentByPhone(string $phone): ?AuthCode;


    public function incrementAttempts(AuthCode $authCode): int;

    public function markAsUsed(AuthCode $authCode): void;

    public function deleteExpired(): int;
}
