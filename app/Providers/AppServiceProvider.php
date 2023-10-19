<?php

namespace App\Providers;

use App\Menu;
use App\Models\Banner;
use App\Models\BannerLocation;
use App\Models\Cardservice;
use App\Models\Category;
use App\Models\Center;
use App\Models\CenterCategory;
use App\Models\ClothesService;
use App\Models\Constructionservice;
use App\Models\Dentistryservice;
use App\Models\Foodservice;
use App\Models\Hotelservice;
use App\Models\Insuranceservice;
use App\Models\Internetservice;
use App\Models\Legalservice;
use App\Models\Physiotherapyservice;
use App\Models\Representation;
use App\Models\Service;
use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }
   /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(Request $request)
    {

        view()->composer(['Admin.layouts.sitemenu' , 'Admin.callcenter.edit' , 'Admin.callcenter.work'], function ($view) {

            $user = auth()->user();
            if ($user->admin_representation){
                $admin_users = User::where('level', 'admin')->where('admin_representation_id' , $user->admin_representation->id)->get();
            }else{
                $admin_users = User::where('level', 'admin')->get();
            }
            foreach ($admin_users as $u) {
                if ($u->hasPermission('callcenter')) {
                    $callcenterusers[] = $u;
                }
            }
            $view->with([
                'callcenter_users' => $callcenterusers,
                'notification' => '0',
            ]);
        });

        view()->composer(['Admin.center.create' , 'Admin.center.edit' ,'Admin.centerRequest.all' , 'Admin.centerRequest.create' , 'Admin.centerRequest.edit'], function ($view) {

            $product_categories = CenterCategory::where( 'type', 'category' )->get();
            $service_categories = CenterCategory::where( 'type', 'service' )->get();

            $categories = Category::where( 'isActive', true )->where( 'depth', 1 )->get();
            $services = Service::all();

            $view->with([
                'product_categories' => $product_categories,
                'service_categories' => $service_categories,
                'categories' => $categories,
                'services' => $services,
            ]);
        });

        view()->composer(['site.representation.index' ,'site.home', 'site.mobile','site.organization.index'], function ($view) {
            $message = 0;
            $factor = 0;
            if (auth()->check()){
                $message = auth()->user()->unreadNotifications->count();
                $factor = auth()->user()->factors()->whereStatus(false)->where('type' , 'online')->where('expired_at', '>=' , now())->count();
            }
            $view->with([
                'message_count' => $message,
                'factor_count' => $factor,
            ]);
        });

        view()->composer(['Admin.session.all','Admin.session.desktop'], function ($view) {
            $admin_users = User::whereHas('employee')->get();
            $centers = Center::all();
            $view->with([
                'admin_users' => $admin_users,
                'centers' => $centers,
            ]);
        });

        view()->composer(['Admin.center.create' , 'Admin.center.edit' ,'Admin.centerRequest.all' , 'Admin.centerRequest.create' , 'Admin.centerRequest.edit'], function ($view) {

            $product_categories = CenterCategory::where( 'type', 'category' )->get();
            $service_categories = CenterCategory::where( 'type', 'service' )->get();

            $categories = Category::where( 'isActive', true )->where( 'depth', 1 )->get();
            $services = Service::all();

            $view->with([
                'product_categories' => $product_categories,
                'service_categories' => $service_categories,
                'categories' => $categories,
                'services' => $services,
            ]);
        });

        view()->composer(['Admin.session.all','Admin.session.desktop','Admin.employee.edit'], function ($view) {
            $admin_users = User::whereHas('employee')->get();
            $centers = Center::all();
            $view->with([
                'admin_users' => $admin_users,
                'centers' => $centers,
            ]);
        });

        // Public Publish

        //notification admin
        view()->composer(['Admin.layouts.navbar'], function ($view) {
            $view->with([
                'notification_user' => '3',
            ]);
        });
        //header site
        view()->composer(['site.layouts.header' , 'site.organization.layouts.header' , 'site.rules' , 'site.organization.master'  , 'site.master' , 'site.organization.footer' , 'site.layouts.footer' , 'site.representation.master', 'site.representation.factor', 'site.organization.index' ,'site.home' ,'site.mobile', 'front.login' ,'site.services.single' , 'site.contact' , 'site.factor' , 'site.agency' , 'site.about' , 'site.checkout','site.singleWebPage.master','site.singleWebPage.index'], function ($view) {

            $menus = Cache::remember('menus', Carbon::now()->addMinute(10), function () {
               return Menu::where('parent_id', 0)->orderBy('sort' , 'asc')->get();
           });

            $setting = Cache::remember('setting', Carbon::now()->addMinute(10), function () {
                return Setting::first();
            });

            $view->with([
                'menus' => $menus,
                'setting' => $setting,
            ]);
        });
        //profile precent
        view()->composer(['site.profile.layouts.index' , 'site.profile.layouts.service'], function ($view) {
            $user = auth()->user();
            $count = 0;
            if ($user->name != '') {
                $count++;
            }
            if ($user->family != '') {
                $count++;
            }
            if ($user->birth_year != '') {
                $count++;
            }
            if ($user->birth_day != '') {
                $count++;
            }
            if ($user->birth_month != '') {
                $count++;
            }
            if ($user->call != '') {
                $count++;
            }
            if ($user->shenasnamecode != '') {
                $count++;
            }

            if ($user->postalcode != '') {
                $count++;
            }
            if ($user->address != '') {
                $count++;
            }
            if ($user->imgshenasname != '') {
                $count++;
            }
            if ($user->avatar != '') {
                $count++;
            }
            if ($user->imgsignature != '') {
                $count++;
            }
            if ($user->email != '') {
                $count++;
            }
            if ($user->serialcharacter != '') {
                $count++;
            }
            if ($user->serialtop != '') {
                $count++;
            }
            if ($user->serialbottom != '') {
                $count++;
            }

            $profile_percent = $count / 16 * 100;

            $view->with([
                'profile_percent' => round($profile_percent),
            ]);

        });

        view()->composer(['Admin.contact.edit','Admin.center.create','Admin.center.edit','Admin.centerRequest.all','Admin.centerRequest.create','Admin.centerRequest.edit','Admin.financialplanstype.all','Admin.financialplanstype.create','Admin.financialplanstype.edit','Admin.resume.all' , 'Admin.resume.create' , 'Admin.resume.edit' , 'Admin.site.slider.create' , 'Admin.site.slider.createMobileOld' , 'Admin.site.slider.edit' , 'Admin.site.article.create', 'Admin.site.article.edit','Admin.recruitmentnotice.create','Admin.recruitmentnotice.edit'], function ($view) {
            $view->with([
                'representations' => Representation::where('status' , true)->get(),
            ]);

        });
        Schema::defaultStringLength(191);
    }

}
