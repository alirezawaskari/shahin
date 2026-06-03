<?php

namespace App\View\Composers;

use App\Models\User;
use Illuminate\Support\Facades\View;

class CallcenterUsersComposer
{
    public function compose(): void
    {
        View::composer(
            ['Admin.layouts.sitemenu', 'Admin.callcenter.edit', 'Admin.callcenter.work'],
            function ($view) {
                $user = auth()->user();

                $admin_users = $user->admin_representation
                    ? User::where('level', 'admin')
                        ->where('admin_representation_id', $user->admin_representation->id)
                        ->get()
                    : User::where('level', 'admin')->get();

                $callcenterusers = $admin_users->filter(fn($u) => $u->hasPermission('callcenter'));

                $view->with([
                    'callcenter_users' => $callcenterusers,
                    'notification' => '0',
                ]);
            }
        );
    }
}
