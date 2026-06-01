<?php

namespace App\View\Composers;

use App\Models\Representation;
use Illuminate\Support\Facades\View;

class RepresentationsComposer
{
    public function compose(): void
    {
        $views = [
            'Admin.factoritem.all',
            'Admin.contact.edit',
            'Admin.center.create',
            'Admin.center.edit',
            'Admin.centerRequest.all',
            'Admin.centerRequest.create',
            'Admin.centerRequest.edit',
            'Admin.financialplanstype.all',
            'Admin.financialplanstype.create',
            'Admin.financialplanstype.edit',
            'Admin.resume.all',
            'Admin.resume.desktop',
            'Admin.resume.create',
            'Admin.resume.edit',
            'Admin.site.slider.create',
            'Admin.site.slider.createMobileOld',
            'Admin.site.slider.edit',
            'Admin.site.article.create',
            'Admin.site.article.edit',
            'Admin.recruitmentnotice.create',
            'Admin.recruitmentnotice.edit',
        ];

        View::composer($views, function ($view) {
            $representations = Cache::remember('active_representations', 3600, function () {
                return Representation::where('status', true)->get();
            });

            $view->with([
                'representations' => $representations,
            ]);
        });
    }
}
