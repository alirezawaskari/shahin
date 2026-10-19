<?php

namespace App\Providers;

use App\Models\Article;
use App\Models\BeforeForm;
use App\Models\Factor;
use App\Models\PhoneList;
use App\Models\Representation;
use App\Models\Service;
use App\Models\ServiceItem;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * This namespace is applied to your controller routes.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = 'App\Http\Controllers';

    public const HOME = '/cart';
    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        Route::model('phonelist' , PhoneList::class);
        Route::model('service' , Service::class);
        Route::model('beforeform' , BeforeForm::class);
        Route::model('representation' , Representation::class);
        Route::model('factor' , Factor::class);
        Route::model('serviceItem' , ServiceItem::class);

        Route::bind('articleSlug' , function ($value) {
            return Article::whereSlug($value)->firstOrFail();
        });

        $this->configureRateLimiting();

        parent::boot();
    }

    /**
     * The "api" limiter referenced by the api middleware group.
     * SMS gateway callbacks (already authenticated by a shared secret) get a higher
     * per-IP ceiling so a delivery-report burst is not throttled.
     */
    protected function configureRateLimiting(): void
    {
        RateLimiter::for('api', function (Request $request) {
            if ($request->is('api/sms/webhook*')) {
                return Limit::perMinute(600)->by('sms-webhook|' . $request->ip());
            }

            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
    }

    /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function map()
    {
        $this->mapApiRoutes();

        $this->mapWebRoutes();

        //
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapWebRoutes()
    {
        Route::middleware('web')
             ->namespace($this->namespace)
             ->group(base_path('routes/web.php'));
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapApiRoutes()
    {
        Route::prefix('api')
             ->middleware('api')
             ->namespace($this->namespace)
             ->group(base_path('routes/api.php'));
    }
}
