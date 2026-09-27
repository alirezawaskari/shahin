<?php

namespace App\View\Composers;

use App\Models\Category;
use App\Models\CenterCategory;
use App\Models\Service;
use Illuminate\Support\Facades\View;

class CenterFormComposer
{
    public function compose(): void
    {
        $views = [
            'Admin.center.create',
            'Admin.center.edit',
            'Admin.centerRequest.all',
            'Admin.centerRequest.create',
            'Admin.centerRequest.edit',
        ];

        View::composer($views, function ($view) {

            $service_categories = Category::filter()->where('parent_id', 2)->where('isActive' , 1)->get();
            $product_categories = Category::filter()->where('parent_id', 1)->where('isActive' , 1)->get();

            $categories = Category::filter()
                ->where('isActive', true)
                ->where('depth', 1)
                ->get();
            $services = Service::filter()->get();

            $view->with([
                'product_categories' => $product_categories,
                'service_categories' => $service_categories,
                'categories' => $categories,
                'services' => $services,
            ]);
        });
    }
}
