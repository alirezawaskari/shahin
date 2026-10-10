<?php

use App\Models\CategoryPermission;
use App\Models\Permission;
use App\Services\V1\Admin\MenuService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 1) Adds the SMS dashboard/overview as the first submenu item under the
     *    unified "پیامک" category (task.md §19), shifting the existing
     *    items created by 2026_10_07_120000_unify_sms_menu_category.php down
     *    by one position.
     *
     * 2) Attaches the "پیامک" category to every shahin_package. The "پیامک"
     *    category was created by 2026_10_07_120000_unify_sms_menu_category.php,
     *    AFTER ShahinPackagesSeeder ran (which attached every
     *    category_permission that existed at seed time to every package via
     *    category_permission_shahin_package) — so, same as an earlier bug on
     *    this branch, the category exists and has permissions, but no
     *    package grants access to it, and it never appears in a business's
     *    menu regardless of role/permission.
     */
    public function up(): void
    {
        if (!Schema::hasTable('category_permissions') || !Schema::hasTable('permissions')) {
            return;
        }

        $smsCategory = CategoryPermission::where('name', 'پیامک')->first();
        if (!$smsCategory) {
            return;
        }

        // 1. Dashboard menu item, first position; shift the rest down by one.
        Permission::where('cat_id', $smsCategory->id)
            ->where('isMenu', true)
            ->where('name', '!=', 'sms-dashboard')
            ->increment('position');

        $roleIds = DB::table('roles')->whereIn('name', ['superAdmin', 'admin'])->pluck('id');

        $dashboardPermission = Permission::updateOrCreate(
            ['name' => 'sms-dashboard'],
            [
                'display_name' => 'پیش‌خوان پیامک',
                'description' => 'پیش‌خوان پیامک',
                'link' => 'admin.sms.dashboard.index',
                'isMenu' => true,
                'cat_id' => $smsCategory->id,
                'position' => 1,
                'is_active' => true,
            ]
        );

        foreach ($roleIds as $roleId) {
            DB::table('permission_role')->updateOrInsert([
                'role_id' => $roleId,
                'permission_id' => $dashboardPermission->id,
            ]);
        }

        // 2. Attach "پیامک" to every package so it actually shows for
        // businesses that have one, the same wiring ShahinPackagesSeeder
        // used for every category that existed when it ran.
        if (Schema::hasTable('shahin_packages') && Schema::hasTable('category_permission_shahin_package')) {
            $packageIds = DB::table('shahin_packages')->pluck('id');

            foreach ($packageIds as $packageId) {
                $exists = DB::table('category_permission_shahin_package')
                    ->where('shahin_package_id', $packageId)
                    ->where('category_permission_id', $smsCategory->id)
                    ->exists();

                if (!$exists) {
                    DB::table('category_permission_shahin_package')->insert([
                        'shahin_package_id' => $packageId,
                        'category_permission_id' => $smsCategory->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        MenuService::flush();
    }

    public function down(): void
    {
        MenuService::flush();
    }
};
