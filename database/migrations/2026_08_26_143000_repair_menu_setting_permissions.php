<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * تنظیمات منو was unreachable for everybody, superAdmin included.
 *
 * Its two permissions (menu_setting, menu_setting_edit) were inserted straight into the
 * database rather than by a seeder, and that insert left them broken twice over:
 *
 *  - cat_id pointed at category 30, which has never existed. MenuService inner-joins
 *    category_permissions, so the "لیست منوها" entry could never appear in the sidebar.
 *  - neither permission was attached to any role, so hasPermission('menu_setting') was
 *    false for every user and /dashboard/menuSetting answered 401.
 *
 * Re-home them under the existing تنظیمات group and grant them to superadmin.
 */
return new class extends Migration
{
    public function up(): void
    {
        $settingsCategory = DB::table('category_permissions')->where('name', 'تنظیمات')->value('id');

        if (! $settingsCategory) {
            return;
        }

        // Only touch rows that are genuinely orphaned, so re-running cannot move anything
        // an administrator has since re-filed by hand.
        $existingCategories = DB::table('category_permissions')->pluck('id');

        DB::table('permissions')
            ->whereIn('name', ['menu_setting', 'menu_setting_edit'])
            ->whereNotIn('cat_id', $existingCategories)
            ->update(['cat_id' => $settingsCategory]);

        $superadmin = DB::table('roles')->where('name', 'superadmin')->value('id');

        if (! $superadmin) {
            return;
        }

        $permissions = DB::table('permissions')
            ->whereIn('name', ['menu_setting', 'menu_setting_edit'])
            ->pluck('id');

        foreach ($permissions as $permissionId) {
            $alreadyGranted = DB::table('permission_role')
                ->where('permission_id', $permissionId)
                ->where('role_id', $superadmin)
                ->exists();

            if (! $alreadyGranted) {
                DB::table('permission_role')->insert([
                    'permission_id' => $permissionId,
                    'role_id'       => $superadmin,
                ]);
            }
        }
    }

    /**
     * Only the grant is reversed. The old cat_id pointed at a category that does not
     * exist, so putting it back would just restore the broken state.
     */
    public function down(): void
    {
        $superadmin = DB::table('roles')->where('name', 'superadmin')->value('id');

        if (! $superadmin) {
            return;
        }

        $permissions = DB::table('permissions')
            ->whereIn('name', ['menu_setting', 'menu_setting_edit'])
            ->pluck('id');

        DB::table('permission_role')
            ->where('role_id', $superadmin)
            ->whereIn('permission_id', $permissions)
            ->delete();
    }
};
