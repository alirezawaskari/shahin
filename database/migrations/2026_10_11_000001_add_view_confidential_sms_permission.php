<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * name => [display_name, description, isMenu, link, position]
     *
     * Action permission (no sidebar link) guarding read access to the body
     * of confidential SMS messages (مالی / حقوقی groups), everywhere message
     * content is returned: list/report AJAX endpoints, exports, and the
     * customer-facing "پیام‌های محرمانه" account section.
     */
    private const PERMISSIONS = [
        'view-confidential-sms' => ['مشاهده متن پیامک‌های محرمانه', 'دسترسی به متن پیامک‌های گروه‌های محرمانه (مالی/حقوقی) در گزارش‌ها، فهرست‌ها و خروجی‌ها', 0, null, 9],
    ];

    public function up(): void
    {
        $categoryId = DB::table('category_permissions')
            ->where('name', 'پیامک')
            ->value('id');

        foreach (self::PERMISSIONS as $name => [$displayName, $description, $isMenu, $link, $position]) {
            $attributes = [
                'name' => $name,
                'display_name' => $displayName,
                'description' => $description,
                'cat_id' => $categoryId,
                'isMenu' => $isMenu,
                'link' => $link,
                'position' => $position,
                'is_active' => 1,
                'updated_at' => now(),
            ];

            $permission = DB::table('permissions')->where('name', $name)->first();

            if ($permission) {
                DB::table('permissions')->where('id', $permission->id)->update($attributes);
                $permissionId = $permission->id;
            } else {
                $permissionId = DB::table('permissions')->insertGetId($attributes + ['created_at' => now()]);
            }

            // Grant to superAdmin/admin by default; other roles can be granted
            // explicitly through the normal permission management screen.
            $roleIds = DB::table('roles')->whereIn('name', ['superAdmin', 'admin'])->pluck('id');
            foreach ($roleIds as $roleId) {
                DB::table('permission_role')->updateOrInsert([
                    'role_id' => $roleId,
                    'permission_id' => $permissionId,
                ]);
            }
        }
    }

    public function down(): void
    {
        $ids = DB::table('permissions')
            ->whereIn('name', array_keys(self::PERMISSIONS))
            ->pluck('id')
            ->all();

        if (! $ids) {
            return;
        }

        DB::transaction(function () use ($ids) {
            DB::table('permission_role')->whereIn('permission_id', $ids)->delete();
            DB::table('permission_user')->whereIn('permission_id', $ids)->delete();
            DB::table('permissions')->whereIn('id', $ids)->delete();
        });
    }
};
