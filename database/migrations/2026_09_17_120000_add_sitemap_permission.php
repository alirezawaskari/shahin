<?php

use App\Services\V1\Admin\MenuService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * ورودی منوی «نقشه سایت» در سایدبار پنل.
 *
 * سایدبار از روی رکوردهای permissions / category_permissions ساخته می‌شود
 * (MenuService)، پس صفحه‌ی نقشه سایت بدون این رکوردها در منو دیده نمی‌شود.
 *
 * MenuService::menuQuery() ابتدا بر اساس category_permissions.position مرتب
 * می‌کند، بنابراین دسته‌ای با position بزرگ‌تر از همه‌ی دسته‌های موجود، آخرین
 * ورودی منو می‌شود — همان جایی که این آیتم باید باشد.
 *
 * superAdmin / admin همه‌ی آیتم‌های منو را می‌بینند و نیازی به اتصال نقش ندارند؛
 * بقیه‌ی نقش‌ها مثل هر دسترسی دیگری از صفحه‌ی نقش‌ها این مورد را می‌گیرند.
 */
return new class extends Migration
{
    private const CATEGORY = 'نقشه سایت';

    private const NAME = 'sitemap';

    public function up(): void
    {
        $categoryId = DB::table('category_permissions')
            ->where('name', self::CATEGORY)
            ->whereNull('deleted_at')
            ->value('id');

        if (! $categoryId) {
            // position برابر بزرگ‌ترین مقدار فعلی + ۱، تا در انتهای منو بنشیند.
            $lastPosition = (int) DB::table('category_permissions')
                ->whereNull('deleted_at')
                ->max('position');

            $categoryId = DB::table('category_permissions')->insertGetId([
                'name'       => self::CATEGORY,
                'icon'       => 'ti-map-alt',
                'is_active'  => 1,
                'position'   => $lastPosition + 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $attributes = [
            'name'         => self::NAME,
            'display_name' => 'ساخت نقشه سایت',
            'description'  => 'ساخت و به‌روزرسانی فایل‌های sitemap.xml سایت اصلی',
            'link'         => 'sitemap.index',
            'isMenu'       => 1,
            'is_active'    => 1,
            'cat_id'       => $categoryId,
            'position'     => 0,
            'updated_at'   => now(),
        ];

        $permission = DB::table('permissions')->where('name', self::NAME)->first();

        if ($permission) {
            DB::table('permissions')->where('id', $permission->id)->update($attributes);
        } else {
            DB::table('permissions')->insert($attributes + ['created_at' => now()]);
        }

        MenuService::flush();
    }

    public function down(): void
    {
        $permission = DB::table('permissions')->where('name', self::NAME)->first();

        if ($permission) {
            DB::table('permission_role')->where('permission_id', $permission->id)->delete();
            DB::table('permission_user')->where('permission_id', $permission->id)->delete();
            DB::table('permissions')->where('id', $permission->id)->delete();
        }

        // دسته فقط وقتی پاک می‌شود که آیتم دیگری زیرش نمانده باشد.
        $categoryId = DB::table('category_permissions')
            ->where('name', self::CATEGORY)
            ->whereNull('deleted_at')
            ->value('id');

        if ($categoryId && ! DB::table('permissions')->where('cat_id', $categoryId)->exists()) {
            DB::table('category_permissions')->where('id', $categoryId)->delete();
        }

        MenuService::flush();
    }
};
