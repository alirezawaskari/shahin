<?php

namespace App\View\Composers;

use Illuminate\Support\Facades\View;

class UserProfileComposer
{
    private array $profileFields = [
        'name',
        'family',
        'birth_year',
        'birth_day',
        'birth_month',
        'melicode',
        'imgshenasname',
        'avatar',
        'email',
    ];

    public function compose(): void
    {
        $views = [
            'site.profile.layouts.index',
            'site.profile.layouts.service',
        ];

        View::composer($views, function ($view) {
            $profile_percent = 0;

            if (auth()->check()) {
                $user = auth()->user();
                $filledFields = 0;

                foreach ($this->profileFields as $field) {
                    if (!empty($user->$field)) {
                        $filledFields++;
                    }
                }

                $totalFields = count($this->profileFields);
                $profile_percent = round(($filledFields / $totalFields) * 100);
            }

            $view->with([
                'profile_percent' => $profile_percent,
            ]);
        });
    }
}
