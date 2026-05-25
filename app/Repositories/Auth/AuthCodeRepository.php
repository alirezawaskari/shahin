<?php

declare(strict_types=1);


namespace App\Repositories\Auth;


use App\Contracts\Auth\OtpRepositoryInterface;
use App\Models\AuthCode;
use Carbon\Carbon;
use Carbon\CarbonInterface;

class AuthCodeRepository implements OtpRepositoryInterface
{

    public function create(string $phone, string $code, CarbonInterface $expiresAt): AuthCode
    {
        return AuthCode::query()->create([
            'phone' => $phone,
            'code' => $code,
            'expires_at' => $expiresAt,
            'attempts' => 0,
            'user_at' => null
        ]);
    }

    public function findActive(string $phone, string $code): ?AuthCode
    {

        return AuthCode::query()
            ->forPhone($phone)
            ->where('code', $code)
            ->active()
            ->latest('id')
            ->first();
    }

    public function findMostRecentByPhone(string $phone): ?AuthCode
    {
        return AuthCode::query()
            ->forPhone($phone)
            ->latest('id')
            ->first();
    }

    public function incrementAttempts(AuthCode $authCode): int
    {
        $authCode->increment('attempts');

        return (int)$authCode->attempts;
    }

    public function markAsUsed(AuthCode $authCode): void
    {
        $authCode->forceFill(['user_at' => Carbon::now()])->save();
    }

    public function deleteExpired(): int
    {
       return AuthCode::query()
           ->where('expires_at','<',Carbon::now())
           ->forceDelete();
    }
}
