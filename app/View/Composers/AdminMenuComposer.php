<?php

namespace App\View\Composers;

use App\Services\V1\Admin\MenuService;
use Illuminate\Support\Facades\View;

class AdminMenuComposer
{
    public function __construct(private MenuService $menuService) {}

    public function compose(): void
    {
        View::composer('Admin.*', function ($view) {
            if (auth()->check()) {
                $view->with('menu', $this->menuService->getMenu(auth()->user()));
            }
        });
    }
}
