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
     * Run the migrations.
     *
     * «view-phone-numbers»: may see recipient phone numbers in the SMS bulk preview and use the
     * «ارسال پیامک» (Direct SMS) button of the requests page. Not a menu item. Granted to the
     * staff roles that had the button before (superAdmin and admin); anyone else gets it from
     * the role/permission screens.
     */
    public function up(): void
    {
        if (!Schema::hasTable('permissions') || !Schema::hasTable('roles')) {
            return;
        }

        $smsCategory = Schema::hasTable('category_permissions')
            ? CategoryPermission::where('name', 'پیامک')->first()
            : null;

        $permission = Permission::updateOrCreate(
            ['name' => 'view-phone-numbers'],
            [
                'display_name' => 'مشاهده شماره تلفن مخاطبین و ارسال پیامک مستقیم',
                'description' => 'مشاهده نمونه شماره‌های مخاطبین در پیش‌نمایش ارسال انبوه و استفاده از دکمه ارسال پیامک مستقیم',
                'isMenu' => false,
                'cat_id' => $smsCategory?->id,
                'is_active' => true,
            ]
        );

        $roleIds = DB::table('roles')->whereIn('name', ['superAdmin', 'admin'])->pluck('id');

        foreach ($roleIds as $roleId) {
            DB::table('permission_role')->updateOrInsert([
                'role_id' => $roleId,
                'permission_id' => $permission->id,
            ]);
        }

        MenuService::flush();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Keep permissions intact
    }
};
