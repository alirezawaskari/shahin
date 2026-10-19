<?php

namespace App\Providers;

use App\View\Composers\AdminMenuComposer;
use App\View\Composers\AdminNavbarComposer;
use App\View\Composers\CallcenterUsersComposer;
use App\View\Composers\CenterFormComposer;
use App\View\Composers\RepresentationsComposer;
use App\View\Composers\SessionDataComposer;
use App\View\Composers\SiteHeaderComposer;
use App\View\Composers\UserProfileComposer;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Signature OTP for the SMS usage contract: 6 digits, sent by the same verify-SMS path as
        // the login OTP but purpose-bound (see SmsContractOtpService).
        $this->app->singleton(\App\Services\Sms\SmsContractOtpService::class, function ($app) {
            $cfg = config('admin_auth.contract_otp');

            return new \App\Services\Sms\SmsContractOtpService(
                new \App\Services\V1\Auth\NumericOtpGenerator((int) $cfg['length']),
                new \App\Services\V1\Auth\SmsOtpNotifier((string) $cfg['sms_template']),
                $app->make(\App\Services\Sms\BulkSmsRecipientFilterService::class),
                (int) $cfg['ttl_seconds'],
                (int) $cfg['max_attempts'],
                (int) $cfg['resend_cooldown_seconds'],
                (int) $cfg['max_sends_per_hour'],
            );
        });

        // SMS top-ups are paid through the project's payment module (Zarinpal / Saman).
        $this->app->bind(
            \App\Services\Sms\SmsPaymentGateway::class,
            \App\Services\Sms\PaymentModuleSmsGateway::class
        );

        $this->app->singleton(
            \App\Contracts\Ai\LlmClientInterface::class,
            \App\Services\Ai\HttpLlmClient::class
        );
    }

    public function boot(): void
    {
        app(AdminMenuComposer::class)->compose();
        app(AdminNavbarComposer::class)->compose();
        app(CallcenterUsersComposer::class)->compose();
        app(CenterFormComposer::class)->compose();
        app(RepresentationsComposer::class)->compose();
        app(SessionDataComposer::class)->compose();
        app(SiteHeaderComposer::class)->compose();
        app(UserProfileComposer::class)->compose();

        Schema::defaultStringLength(191);
    }
}
