<?php

declare(strict_types=1);


namespace App\Providers;

use App\Contracts\Auth\OtpGeneratorInterface;
use App\Contracts\Auth\OtpNotifierInterface;
use App\Contracts\Auth\OtpRepositoryInterface;
use App\Repositories\Auth\AuthCodeRepository;
use App\Services\V1\Auth\NumericOtpGenerator;
use App\Services\V1\Auth\OtpService;
use App\Services\V1\Auth\SmsOtpNotifier;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;

final class AdminAuthProvider extends ServiceProvider
{


    public function register(): void
    {

        $this->bindContracts();
        $this->bindOtpServiceParameters();
        $this->bindStatefulGuard();


    }

    public function boot()
    {

    }

    private function bindContracts(): void
    {
        $this->app->bind(
            OtpRepositoryInterface::class,
            AuthCodeRepository::class
        );

        $this->app->bind(OtpGeneratorInterface::class, function ($app) {
            return new NumericOtpGenerator(
                (int)config('admin_auth.otp.length', 6)
            );
        });

        $this->app->bind(OtpNotifierInterface::class, function ($app) {
            return new SmsOtpNotifier(
                (string)config('admin_auth.otp.sms_template', 'admin-login-otp')
            );
        });

    }

    private function bindOtpServiceParameters(): void
    {
        $this->app->when(OtpService::class)
            ->needs('$ttlSeconds')
            ->give(fn() => (int)config('admin_auth.otp.ttl_seconds', 120));

        $this->app->when(OtpService::class)
            ->needs('$maxAttempts')
            ->give(fn() => (int)config('admin_auth.otp.max_attempts', 5));

        $this->app->when(OtpService::class)
            ->needs('$resendCooldownSeconds')
            ->give(fn() => (int)config('admin_auth.otp.resend_cooldown_seconds', 90));

    }

    private function bindStatefulGuard(): void
    {
        $this->app->bind(StatefulGuard::class, function () {
            return Auth::guard();
        });

    }

}
