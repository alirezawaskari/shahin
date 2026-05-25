<?php

declare(strict_types=1);


namespace App\Providers;

use App\Contracts\Auth\OtpGeneratorInterface;
use App\Contracts\Auth\OtpNotifierInterface;
use App\Contracts\Auth\OtpRepositoryInterface;
use App\Repositories\Auth\AuthCodeRepository;
use App\Services\V1\Auth\NumericOtpGenerator;
use App\Services\V1\Auth\SmsOtpNotifier;
use Illuminate\Support\ServiceProvider;

final class AdminAuthProvider extends ServiceProvider
{

    public function register()
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

    public function boot()
    {

    }

}
