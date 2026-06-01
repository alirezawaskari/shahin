<?php

namespace App\View\Composers;

use App\Models\User;
use Illuminate\Support\Facades\View;

class AdminNavbarComposer
{
    public function compose(): void
    {
        View::composer('Admin.layouts.navbar', function ($view) {

            $notificationCount = 0;

            if (auth()->check()) {
                $notificationCount = 3;
            }

            $view->with([
                'notification_user' => $notificationCount,
            ]);
        });
    }
}
