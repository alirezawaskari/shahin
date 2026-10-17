<?php

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Ensure permissions exist
        $permissions = [
            'sms-template' => 'مشاهده قالب‌های پیامک',
            'sms-template-create' => 'ایجاد قالب پیامک جدید',
            'sms-template-edit' => 'ویرایش قالب‌های پیامک',
        ];

        $permissionIds = [];
        foreach ($permissions as $permName => $displayName) {
            $perm = Permission::firstOrCreate(
                ['name' => $permName],
                [
                    'display_name' => $displayName,
                    'description' => $displayName,
                ]
            );
            $permissionIds[] = $perm->id;
        }

        // 2. Attach permissions to superAdmin and ghorfeAdmin roles
        $targetRoles = Role::whereIn('name', ['superAdmin', 'ghorfeAdmin'])->get();

        foreach ($targetRoles as $role) {
            foreach ($permissionIds as $permId) {
                $exists = DB::table('permission_role')
                    ->where('permission_id', $permId)
                    ->where('role_id', $role->id)
                    ->exists();

                if (!$exists) {
                    DB::table('permission_role')->insert([
                        'permission_id' => $permId,
                        'role_id' => $role->id,
                    ]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Keep permissions intact
    }
};
