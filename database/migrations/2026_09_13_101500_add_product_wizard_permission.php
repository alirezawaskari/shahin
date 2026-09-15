<?php

use App\Services\V1\Admin\MenuService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * سایدبار پنل از روی رکوردهای `permissions` ساخته می‌شود (MenuService / AdminMenuComposer)،
 * پس «ویزارد ایجاد محصول» تا وقتی رکوردی اینجا نداشته باشد در منو دیده نمی‌شود.
 * این رکورد زیر دسته محصولات (cat_id 10) ثبت می‌شود.
 *
 * superAdmin / admin همه آیتم‌های منو را می‌بینند و نیازی به اتصال نقش ندارند؛ بقیه نقش‌ها
 * مثل هر دسترسی دیگری از صفحه نقش‌ها این مورد را می‌گیرند.
 */
return new class extends Migration
{
    private const NAME = 'product-wizard';

    public function up(): void
    {
        if (DB::table('permissions')->where('name', self::NAME)->exists()) {
            return;
        }

        DB::table('permissions')->insert([
            'name' => self::NAME,
            'display_name' => 'ویزارد ایجاد محصول',
            'description' => 'ایجاد مرحله‌ای دسته‌بندی، کالا/خدمت و تنوع کالا در یک مسیر پیوسته',
            'link' => 'productWizard.index',
            'isMenu' => 1,
            'is_active' => 1,
            'cat_id' => 10,
            'position' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        MenuService::flush();
    }

    public function down(): void
    {
        $permission = DB::table('permissions')->where('name', self::NAME)->first();

        if (!$permission) {
            return;
        }

        DB::table('permission_role')->where('permission_id', $permission->id)->delete();
        DB::table('permission_user')->where('permission_id', $permission->id)->delete();
        DB::table('permissions')->where('id', $permission->id)->delete();

        MenuService::flush();
    }
};
