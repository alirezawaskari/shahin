<?php

namespace App\Providers;

use App\Models\Area;
use App\Models\Category;
use App\Models\Center;
use App\Models\Organization;
use App\Models\Province;
use App\Models\Reason;
use App\Models\RecruitmentNotice;
use App\Models\Representation;
use App\Models\Role;
use App\Models\Service;
use App\Models\User;
use Illuminate\Support\ServiceProvider;

class FilterServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        // Feeds the master "please accept the terms" banner (Admin.layouts.terms) on every
        // admin page. Sourced from the EmployeeTerm model (Spatie-first, Context fallback).
        view()->composer(['Admin.master'], function ($view) {
            $view->with('role_categories_with_terms', \App\Models\EmployeeTerm::rolesWithTerms());
        });
        view()->composer(['Admin.center.all','Admin.callcenter.sendLink' , 'Admin.callcenter.all','Admin.factoritem.all','Admin.factor.all','Admin.factor.desktop','Admin.totalfactoritem.all','Admin.totalfactoritem.report'], function ($view) {
            $data =$this->tasks_requires();
            $view->with($data);
        });
        // «ارسال پیامک به نتیجه فیلتر» on the requests page, and the requests-filter pop-up of the
        // group-message page: both reuse the requests page's filter data / send data.
        view()->composer(['Admin.callcenter.partials.sms-to-filter-modal'], \App\View\Composers\SmsToFilterComposer::class);
        view()->composer(['Admin.callcenter.partials.filter-modal'], function ($view) {
            $view->with($this->tasks_requires());
        });
        view()->composer(['Admin.accounting.all','Admin.accounting.installments'], function ($view) {
            $view->with([
                'centers' => Center::all(),
            ]);
        });
        view()->composer(['Admin.user.all'], function ($view) {
            $view->with([
                'provinces' => Province::all(),
                'centers' => Center::where('status' , 1)->get(),
                'representations' => Representation::all(),
                'organizations' => Organization::all(),
                'roles' => Role::all(),
            ]);
        });
        view()->composer(['Admin.employee.all','Admin.employee.financialReportEmployeeList'], function ($view) {
            $view->with([
                'roles' => Role::all(),
            ]);
        });
        view()->composer(['Admin.resume.all','Admin.resume.desktop'], function ($view) {
            $view->with([
                'jobs' =>  RecruitmentNotice::all(),
            ]);
        });
    }

    public function tasks_requires()
    {
        $user = auth()->user();
        $role_users = Role::where('name', 'callcenter')->orWhere('name', 'presentore')->orWhere('name', 'influencer-marketing')->with('users')->get();
        $admin_users = User::filter()->whereIn('level', ['admin','ghorfeAdmin'])->withCount('tasks')->get();

        $services = Service::filter()->where('status' , 1)->get();
        // invoiceRows() rather than filter(): a business whose categories were never ticked
        // into category_ghorfe_online_list still gets the categories it actually sells in,
        // instead of an empty picker it cannot raise a single invoice from.
        $product_categories = Category::invoiceRows(1)->get();
        $service_categories = Category::invoiceRows(2)->get();
        $permission = false;

        if (auth()->user()->center_id && !auth()->user()->hasPermission('center-all-services')) {
            $services = Service::filter()->whereIn('id' , auth()->user()->center->services->pluck('id')->toArray())->get();
            $service_categories = auth()->user()->center->centerable_type == Service::class && auth()->user()->center->centerable ? Category::where('id', auth()->user()->center->centerable->category_id)->get() : collect([]);
            $product_categories = Category::filter()->where('parent_id', 1)->where('id', auth()->user()->center->centerable_id)->get();
        }elseif ($user->admin_representation) {
            $admin_users = User::filter()->where('level', 'admin')->where('admin_representation_id', $user->admin_representation->id)->withCount('tasks')->get();
        }
        if($user->isAbleTo('record_payment'))
        {
            $permission = true;
        }

        $old_callcenters = [];

        foreach ($role_users as $users) {
            foreach ($users->users as $u) {
                if ($u->level == 'user') {
                    $old_callcenters[] = $u;
                }
            }
        }

        $callcenters = [];
        foreach ($admin_users as $u) {
            $callcenters[] = $u;
        }

        return [
            'services' => $services->map(function ($item) {
                $temp = [];
                $temp['id'] = $item->id;
                $temp['label'] = $item->title /*. ' - ' . number_format($item->price) . ' ریال'*/;
                return $temp;
            }),
            'service_categories' => $service_categories->map(function ($item) {
                $temp = [];
                $temp['id'] = $item->id;
                $temp['label'] = $item->title;
                return $temp;
            }),
            'product_categories' => $product_categories->map(function ($item) {
                $temp = [];
                $temp['id'] = $item->id;
                $temp['label'] = $item->title . ' - ' . number_format($item->price) . ' ریال';
                return $temp;
            }),
            'callcenters' => $callcenters,
            'old_callcenters' => $old_callcenters,
            'reasons' => Reason::where('id', '!=', 0)->get(),
            'provinces' => Province::all(),
            'areas' => Area::all(),
            'centers' => Center::filter()->where('status' , 1)->get(),
            'representations' => Representation::all(),
            'organizations' => Organization::all(),
            'recruitment_notices' => RecruitmentNotice::all(),
            'permission' => $permission,
        ];
    }
}
