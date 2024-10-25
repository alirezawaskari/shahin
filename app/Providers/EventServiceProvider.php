<?php

namespace App\Providers;

use App\Events\checkSmsReminder;
use App\Events\ExtensionDate;
use App\Events\fastregisterCallcenter;
use App\Events\fastregisterorganCallcenter;
use App\Events\fastserviceCallcenter;
use App\Events\sendlinkFactor;
use App\Events\factorItemActivation;
use App\Events\factorItemStartWorking;
use App\Events\factoritemClosing;
use App\Events\smsSuccessRegisterPayment;
use App\Events\ticketNotification;
use App\Events\referralCenterNotification;
use App\Events\smsService;
use App\Events\smsServiceSession;
use App\Events\successFactor;
use App\Events\userActivation;
use App\Events\smsNotAnswer;
use App\Listeners\ExtensionSmsReminder;
use App\Listeners\FactorItemActivationSMSNotificaion;
use App\Listeners\FactorItemStartWorkingSMSNotificaion;
use App\Listeners\FactorItemClosingSMSNotificaion;
use App\Listeners\ReferralCenterSMSNotification;
use App\Listeners\SendSmsCheckReminder;
use App\Listeners\SendSmsSuccessRegisterPayment;
use App\Listeners\TicketSMSNotification;
use App\Listeners\RegCallcenterSMSNotification;
use App\Listeners\RegOrganSMSNotification;
use App\Listeners\SendSMSNotificaion;
use App\Listeners\ServiceCallcenterSMSNotification;
use App\Listeners\smsServiceNotification;
use App\Listeners\smsServiceSessionNotification;
use App\Listeners\SuccessFactorSMSNotification;
use App\Listeners\SendLinkSMSNotification;
use App\Listeners\SendSmsNotAnswer;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        userActivation::class => [
            SendSMSNotificaion::class,
        ],
        smsNotAnswer::class => [
            SendSmsNotAnswer::class,
        ],
        successFactor::class => [
            SuccessFactorSMSNotification::class,
        ],
        smsService::class => [
            smsServiceNotification::class,
        ],
        smsServiceSession::class => [
            smsServiceSessionNotification::class,
        ],
        fastregisterCallcenter::class => [
            RegCallcenterSMSNotification::class,
        ],
		fastregisterorganCallcenter::class => [
            RegOrganSMSNotification::class,
        ],
        fastserviceCallcenter::class => [
            ServiceCallcenterSMSNotification::class,
        ],
        factorItemActivation::class => [
            FactorItemActivationSMSNotificaion::class,
        ],
        factorItemStartWorking::class => [
            FactorItemStartWorkingSMSNotificaion::class,
        ],
        factorItemClosing::class => [
            FactorItemClosingSMSNotificaion::class,
        ],
        sendlinkFactor::class => [
            SendLinkSMSNotification::class,
        ],
        ticketNotification::class => [
            TicketSMSNotification::class,
        ],
        referralCenterNotification::class => [
            ReferralCenterSMSNotification::class,
        ],
        smsSuccessRegisterPayment::class => [
            SendSmsSuccessRegisterPayment::class,
        ],
        checkSmsReminder::class => [
            SendSmsCheckReminder::class,
        ],
        ExtensionDate::class => [
            ExtensionSmsReminder::class,
        ]
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        parent::boot();

        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
