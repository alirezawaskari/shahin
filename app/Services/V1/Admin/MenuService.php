<?php

namespace App\Services\V1\Admin;

use App\Models\CategoryPermission;
use App\Models\GhorfeOnlineList;
use App\Models\Permission;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;

class MenuService
{
    public function getMenu($user)
    {
        if ($user->level == 'superAdmin' || $user->level == 'admin') {
            return Cache::remember('menu_super_admin', 60, fn() => $this->getAllMenus());
        }

        return Cache::remember("menu_user_{$user->id}", 60, function () use ($user) {

            $currentRoute = Route::currentRouteName();
            $packagePermissions = $this->getUserPackagePermissions($user);
            $permissions = Permission::query()
                ->with('categoryPermissions')
                ->where('isMenu', true)
                ->whereHas('categoryPermissions', function ($query) use ($packagePermissions) {
                    $query->whereIn('id', $packagePermissions);
                })
                ->get();

            return $this->groupByCategory($permissions);
        });
    }

    /**
     * گرفتن permission های پکیج
     */
    private function getUserPackagePermissions($user)
    {
        if ($user->level !== 'ghorfeAdmin') {
            return [];
        }

        $currentDomain = $this->normalizeDomain(request()->getHost());

        $ghorfe = $user->ghorfeOnlineLists()
            ->with('shahinPackage.categoryPermission:id') // 🔥 فقط id
            ->get()
            ->first(fn($item) =>
                $this->normalizeDomain($item->domain_active) === $currentDomain
            );

        if (!$ghorfe?->shahinPackage) {
            return [];
        }
        return $ghorfe->shahinPackage
            ->categoryPermission
            ->pluck('id')
            ->toArray();
    }

    /**
     * همه منوها
     */
    private function getAllMenus()
    {
        $currentRoute = Route::currentRouteName();
        return
            CategoryPermission::with('permission')
                ->where('is_active','!=',0)
                ->get()
                ->map(fn($p) => $this->mapPermission($p, $currentRoute));
    }

    /**
     * map کردن permission
     */
    private function mapPermission($permission, $currentRoute)
    {
        $permission->is_active = $this->isActive($permission->route_name, $currentRoute);
        return $permission;
    }

    /**
     * group بندی
     */
    private function groupByCategory($permissions)
    {
        return $permissions->groupBy(function ($item) {
            return $item->categoryPermissions?->name ?? 'بدون دسته‌بندی';
        })->map(function ($items, $categoryName) {
            return [
                'name' => $categoryName,
                'icon' => $items->first()->categoryPermissions?->icon ?? '',
                'items' => $items
            ];
        });
    }
    /**
     * active check (بهینه)
     */
    private function isActive($routes, $currentRoute)
    {
        if (!$routes) return false;

        if (is_array($routes)) {
            return in_array($currentRoute, $routes);
        }

        return str_starts_with($currentRoute, $routes);
    }

    /**
     * normalize domain
     */
    private function normalizeDomain($domain)
    {
        return preg_replace('#^https?://#', '', str_replace('www.', '', $domain));
    }
}
