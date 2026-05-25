<?php
declare(strict_types=1);

namespace App\Services\V1\Auth;

use App\DTOs\Auth\OtpVerificationDTO;
use App\DTOs\Auth\PasswordCredentialsDTO;
use App\Exceptions\Auth\InactiveAccountException;
use App\Exceptions\Auth\InvalidAdminCredentialsException;
use App\Exceptions\Auth\NotAdminException;
use App\Exceptions\Auth\OtpNotificationException;
use App\Exceptions\Auth\TooManyOtpAttemptsException;
use App\Exceptions\Auth\TooManyOtpRequestsException;
use App\Models\User;
use Illuminate\Contracts\Auth\StatefulGuard;

final readonly class AdminAuthService
{
    public function __construct(
        private PasswordAuthenticator $passwordAuthenticator,
        private OtpService            $otpService,
        private StatefulGuard         $guard,
    )
    {
    }


    /**
     * @throws InvalidAdminCredentialsException
     * @throws InactiveAccountException
     * @throws NotAdminException
     */
    public function loginByPassword(PasswordCredentialsDTO $credentials): User
    {
        $user = $this->passwordAuthenticator->authenticate($credentials);

        $this->guard->login($user);

        return $user;

    }

    /**
     * @throws NotAdminException
     * @throws OtpNotificationException
     * @throws TooManyOtpRequestsException
     */
    public function requestOtp(string $phone): int
    {
        return $this->otpService->sendCodeFor($phone);
    }

    /**
     * @throws TooManyOtpAttemptsException
     * @throws NotAdminException
     */
    public function loginByOtp(OtpVerificationDTO $verification): User
    {
        $user = $this->otpService->verifyCode(
            $verification->phone,
            $verification->code
        );

        $this->guard->login($user);

        return $user;
    }

    public function logout(): void
    {
        $this->guard->logout();
    }
}
