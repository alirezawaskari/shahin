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
        view()->composer(['Admin.callcenter.sendLink' , 'Admin.callcenter.all','Admin.factoritem.all','Admin.factor.all','Admin.factor.desktop','Admin.totalfactoritem.all','Admin.totalfactoritem.report'], function ($view) {
            $data =$this->tasks_requires();
            $view->with($data);
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
        $admin_users = User::whereDoesntHave('ghorfeOnlineLists')->where('level', 'admin')->withCount('tasks')->get();
        $services = Service::filter()->where('status' , 1)->get();
        $product_categories = Category::filter()->where('parent_id', 1)->where('isActive' , 1)->get();
        $service_categories = Category::filter()->where('parent_id', 2)->where('isActive' ,1)->get();
        $permission = false;

        if (auth()->user()->center_id && !auth()->user()->hasPermission('center-all-services')) {
            $services = Service::filter()->whereIn('id' , auth()->user()->center->services->pluck('id')->toArray())->get();
            $service_categories = auth()->user()->center->centerable_type == Service::class && auth()->user()->center->centerable ? Category::where('id', auth()->user()->center->centerable->category_id)->get() : collect([]);
            $product_categories = Category::filter()->where('parent_id', 1)->where('id', auth()->user()->center->centerable_id)->get();
        }elseif ($user->admin_representation) {
            $admin_users = User::whereDoesntHave('ghorfeOnlineLists')->where('level', 'admin')->where('admin_representation_id', $user->admin_representation->id)->withCount('tasks')->get();
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
            'centers' => Center::where('status' , 1)->get(),
            'representations' => Representation::all(),
            'organizations' => Organization::all(),
            'recruitment_notices' => RecruitmentNotice::all(),
            'permission' => $permission,
        ];
    }
}
