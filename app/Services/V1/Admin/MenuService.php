<?php

namespace App\Services\V1\Admin;

use App\Models\Permission;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;

class MenuService
{
    /**
     * `permissions.name` هایی که فقط superAdmin در منو می‌بیند.
     *
     * admin هم مثل superAdmin همه‌ی آیتم‌های منو را می‌گیرد، پس نگه داشتن یک
     * آیتم از او با داده‌ی جدول permissions ممکن نیست و باید همین‌جا کنار گذاشته
     * شود. کش این دو نقش هم به همین دلیل کلید جداگانه دارد.
     */
    public const SUPER_ADMIN_ONLY = [
        // نقشه سایتِ سایت اصلی؛ کار ستاد است نه هر مدیر پنل.
        'sitemap',
    ];

    public function getMenu($user)
    {
        $version = $this->version();

        // isSuperAdmin و نه level: حساب‌هایی که نقش «superadmin» دارند و level شان
        // admin است هم باید همان منوی کامل را ببینند، وگرنه آیتمی که مسیرش برایشان
        // باز است در منو غایب می‌ماند.
        if ($user->isSuperAdmin()) {
            return Cache::remember("menu_super_admin_{$version}", 60, fn() => $this->getAllMenus());
        }

        if ($user->level == 'admin') {
            return Cache::remember("menu_admin_{$version}", 60, fn() => $this->getAllMenus(self::SUPER_ADMIN_ONLY));
        }

        return Cache::remember("menu_user_{$user->id}_{$version}", 60, function () use ($user) {

            $packagePermissions = $this->getUserPackagePermissions($user);

            return $this->groupByCategory(
                $this->menuQuery()->whereIn('category_permissions.id', $packagePermissions)->get()
            );
        });
    }

    /**
     * The cached menus are keyed by this stamp, so bumping it retires every user's copy at
     * once — there is no way to enumerate the per-user keys, and the default cache store
     * has no tags. Called whenever تنظیمات منو writes.
     */
    public static function flush(): void
    {
        Cache::forever('menu_version', (string) microtime(true));
    }

    private function version(): string
    {
        return (string) Cache::get('menu_version', '0');
    }

    /**
     * گرفتن permission های پکیج
     */
    private function getUserPackagePermissions($user)
    {
        if ($user->level !== 'ghorfeAdmin') {
            return [];
        }

        $ghorfe = ghorfe();

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
     *
     * @param array<int, string> $except نام دسترسی‌هایی که نباید در منو بیایند
     */
    private function getAllMenus(array $except = [])
    {
        $query = $this->menuQuery();

        if ($except !== []) {
            $query->whereNotIn('permissions.name', $except);
        }

        return $this->groupByCategory($query->get());
    }

    /**
     * The menu, in the order تنظیمات منو defines: groups by category_permissions.position,
     * then the items inside each group by permissions.position.
     *
     * select() is not optional here. The join brings a second `id`, `name`, `is_active`,
     * `position`, `created_at` and `updated_at` into the row, and the driver keeps the last
     * one — so without it every Permission would report the *category's* id, name and
     * position, and ordering the items would silently do nothing.
     */
    private function menuQuery()
    {
        return Permission::query()
            ->select('permissions.*')
            ->join('category_permissions', 'category_permissions.id', '=', 'permissions.cat_id')
            ->with('categoryPermissions')
            ->where('category_permissions.is_active', true)
            ->where('permissions.isMenu', true)
            ->orderBy('category_permissions.position')
            ->orderBy('category_permissions.name')
            ->orderBy('permissions.position')
            ->orderBy('permissions.id');
    }

    /**
     * Turn a permission's stored route name into a URL, or null when it cannot produce one.
     *
     * Route::has() alone is not enough: a name like `menuSetting.edit` exists but needs a
     * {id}, and route() would throw UrlGenerationException while rendering the sidebar of
     * every page. Anything that cannot be built is simply not a menu link.
     */
    public static function linkToUrl(?string $name): ?string
    {
        if (blank($name) || ! Route::has($name)) {
            return null;
        }

        try {
            return route($name);
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * group بندی
     *
     * Items whose link cannot be resolved are dropped rather than rendered — otherwise one
     * bad row takes the whole panel down. تنظیمات منو refuses to enable an item without a
     * usable link, so this only catches rows predating that rule.
     */
    private function groupByCategory($permissions)
    {
        return $permissions
            ->each(fn($item) => $item->menu_url = static::linkToUrl($item->link))
            ->filter(fn($item) => filled($item->menu_url))
            ->groupBy(fn($item) => $item->categoryPermissions?->name ?? 'بدون دسته‌بندی')
            ->map(function ($items, $categoryName) {
                return [
                    'name' => $categoryName,
                    'icon' => $items->first()->categoryPermissions?->icon ?? '',
                    'items' => $items->values(),
                ];
            });
    }
}
