<?php

declare(strict_types=1);

namespace App\Services\V1\Auth;

use App\Contracts\Auth\OtpGeneratorInterface;
use App\Contracts\Auth\OtpNotifierInterface;
use App\Contracts\Auth\OtpRepositoryInterface;
use App\Exceptions\Auth\InvalidOtpException;
use App\Exceptions\Auth\NotAdminException;
use App\Exceptions\Auth\OtpAlreadyUsedException;
use App\Exceptions\Auth\OtpExpiredException;
use App\Exceptions\Auth\TooManyOtpAttemptsException;
use App\Exceptions\Auth\TooManyOtpRequestsException;
use App\Models\User;
use Carbon\Carbon;

final readonly class OtpService
{

    public function __construct(
        private OtpRepositoryInterface $repository,
        private OtpGeneratorInterface  $generator,
        private OtpNotifierInterface   $notifier,
        private int                    $ttlSeconds,
        private int                    $maxAttempts,
        private int                    $resendCooldownSeconds,

    )
    {

    }

    public function sendCodeFor(string $phone): int
    {
        //check is user admin
        $this->ensurePhoneBelongsToActiveAdmin($phone);

        //cooldown
        $this->ensureNotInCooldown($phone);

        $code = $this->generator->generate();

        $expiresAt = Carbon::now()->addSeconds($this->ttlSeconds);
        $this->repository->create($phone, $code, $expiresAt);

        $this->notifier->send($phone, $code);

        return $this->ttlSeconds;

    }


    /**
     * @throws TooManyOtpAttemptsException
     * @throws NotAdminException
     */
    public function verifyCode(string $phone, string $code): User
    {

        $authCode = $this->repository->findActive($phone, $code);
        if ($authCode === null) {
            $this->handleNoActiveMatch($phone, $code);
        }

        if ($authCode->hasExceededAttempts($this->maxAttempts)) {
            throw new TooManyOtpAttemptsException();
        }

        $user = $this->fetchActiveAdmin($phone);

        $this->repository->markAsUsed($authCode);


        return $user;
    }

    /**
     * @throws NotAdminException
     */
    private function ensurePhoneBelongsToActiveAdmin($phone): void
    {
        $this->fetchActiveAdmin($phone);
    }

    /**
     * @param $phone
     * @return User
     * @throws NotAdminException
     */
    private function fetchActiveAdmin($phone): User
    {
        $user = User::query()->where('phone', $phone)->first();

        if ($user === null || !$user->isAdmin() || !$user->isActive()) {
            throw new NotAdminException();
        }

        return $user;
    }

    private function ensureNotInCooldown(string $phone): void
    {
        $recent = $this->repository->findMostRecentByPhone($phone);

        if ($recent === null) {
            return;
        }

        $elapsed = $recent->secondsSinceCreated();

        if ($elapsed < $this->resendCooldownSeconds) {
            $retryAfter = $this->resendCooldownSeconds - $elapsed;
            throw new TooManyOtpRequestsException($retryAfter);
        }
    }

    private function handleNoActiveMatch(string $phone, string $code)
    {

        $recent = $this->repository->findMostRecentByPhone($phone);

        if ($recent === null) {
            throw new InvalidOtpException();
        }

        if ($recent->code === $code) {
            if ($recent->isUsed()) {
                throw new OtpAlreadyUsedException();
            }
            if ($recent->isExpired()) {
                throw new OtpExpiredException();
            }
        }

        if ($recent->isActive()) {
            $attempts = $this->repository->incrementAttempts($recent);

            if ($attempts >= $this->maxAttempts) {
                // سوزاندن کد تا دیگر قابل استفاده نباشد
                $this->repository->markAsUsed($recent);
                throw new TooManyOtpAttemptsException();
            }
        }

        throw new InvalidOtpException();
    }


}
