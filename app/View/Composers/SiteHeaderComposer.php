<?php

namespace App\View\Composers;

use App\Menu;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;

class SiteHeaderComposer
{
    public function compose(): void
    {
        $views = [
            'site.layouts.header', 'site.organization.layouts.header',
            'site.rules', 'site.organization.master', 'site.master',
            'site.organization.footer', 'site.layouts.footer',
            'site.representation.master', 'site.representation.factor',
            'site.organization.index', 'site.home', 'site.mobile',
            'front.login', 'site.services.single', 'site.contact',
            'site.factor', 'site.agency', 'site.about', 'site.checkout',
            'site.singleWebPage.master', 'site.singleWebPage.index',
        ];

        View::composer($views, function ($view) {
            $menus = Cache::remember('menus', Carbon::now()->addMinute(10), function () {
                return Menu::where('parent_id', 0)->orderBy('sort', 'asc')->get();
            });

            $setting = Cache::remember('setting', Carbon::now()->addMinute(10), function () {
                return Setting::first();
            });

            $view->with(compact('menus', 'setting'));
        });
    }
}
