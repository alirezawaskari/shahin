<?php

namespace App\View\Composers;

use App\Models\Center;
use App\Models\User;
use Illuminate\Support\Facades\View;

class SessionDataComposer
{
    public function compose(): void
    {
        $views = [
            'Admin.session.all',
            'Admin.session.desktopConfirm',
            'Admin.session.desktopComplete',
            'Admin.session.desktop',
            'Admin.percentageAllocationEmployee.create',
            'Admin.employee.edit',
        ];

        View::composer($views, function ($view) {

            $admin_users = User::whereHas('employee')->get();
            $centers = Center::where('status', true)->get();

            $view->with([
                'admin_users' => $admin_users,
                'centers' => $centers,
            ]);
        });
    }
}
