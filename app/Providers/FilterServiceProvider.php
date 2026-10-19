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
        view()->composer(['Admin.callcenter.partials.filter-modal', 'Admin.callcenter.partials.filter-fields'], function ($view) {
            // The SMS audience pop-up pins the audience to the sending business (Callcenter::accessScoped),
            // so its expert / marketer lists may only offer that business's own users — for every viewer,
            // super-admin included (an out-of-business pick could only ever match nothing).
            $business = $view->getData()['restrictUsersToBusiness'] ?? null;
            $view->with($this->tasks_requires($business instanceof \App\Models\GhorfeOnlineList ? $business : null));
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

    public function tasks_requires(?\App\Models\GhorfeOnlineList $onlyBusiness = null)
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

        if ($user && $user->center_id && !$user->hasPermission('center-all-services')) {
            $services = Service::filter()->whereIn('id' , $user->center->services->pluck('id')->toArray())->get();
            $service_categories = $user->center->centerable_type == Service::class && $user->center->centerable ? Category::where('id', $user->center->centerable->category_id)->get() : collect([]);
            $product_categories = Category::filter()->where('parent_id', 1)->where('id', $user->center->centerable_id)->get();
        } elseif ($user && $user->admin_representation) {
            $admin_users = User::filter()->where('level', 'admin')->where('admin_representation_id', $user->admin_representation->id)->withCount('tasks')->get();
        }
        if ($user && $user->isAbleTo('record_payment')) {
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

        if ($onlyBusiness) {
            $admin_users = self::onlyBusinessMembers($admin_users, $onlyBusiness);
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

    /** Keep only the users that belong to the business (see businessUserIds()). */
    public static function onlyBusinessMembers($users, \App\Models\GhorfeOnlineList $business)
    {
        $memberIds = self::businessUserIds($business);

        return $users->filter(fn ($u) => in_array((int) $u->id, $memberIds, true))->values();
    }

    /**
     * Ids of the staff users that belong to the business: attached through the business pivot
     * (ghorfe_online_list_user, what User::filter() scopes by) or employed by it.
     *
     * @return int[]
     */
    public static function businessUserIds(\App\Models\GhorfeOnlineList $business): array
    {
        $pivot = $business->users()->pluck('users.id')->all();
        $employees = \App\Models\Employee::where('ghorfe_online_list_id', $business->id)->whereNotNull('user_id')->pluck('user_id')->all();

        return array_map('intval', array_values(array_unique(array_merge($pivot, $employees))));
    }
}
