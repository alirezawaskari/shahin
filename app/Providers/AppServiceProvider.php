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
        //
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
