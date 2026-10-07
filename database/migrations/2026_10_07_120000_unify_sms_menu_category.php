<?php

use App\Models\CategoryPermission;
use App\Models\Permission;
use App\Services\V1\Admin\MenuService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('category_permissions') || !Schema::hasTable('permissions')) {
            return;
        }

        // 1. Locate or create singular unified "پیامک" category
        $smsCategory = CategoryPermission::where('name', 'پیامک')->first();
        if (!$smsCategory) {
            $smsCategory = CategoryPermission::create([
                'name' => 'پیامک',
                'icon' => 'ti-comment-alt',
                'is_active' => true,
                'position' => 28,
            ]);
        } else {
            $smsCategory->update([
                'icon' => 'ti-comment-alt',
                'is_active' => true,
            ]);
        }

        // 2. Find legacy "قالب های پیامکی" category and deactivate it, reassigning permissions
        $templateCategory = CategoryPermission::where('name', 'قالب های پیامکی')->first();
        if ($templateCategory) {
            Permission::where('cat_id', $templateCategory->id)->update([
                'cat_id' => $smsCategory->id,
            ]);

            $templateCategory->update([
                'is_active' => false,
            ]);
        }

        // Deactivate create template from top-level sidebar menu (accessible via template list)
        Permission::where('name', 'sms-template-create')->update([
            'isMenu' => false,
            'cat_id' => $smsCategory->id,
        ]);

        // 3. Define the unified submenus under the singular SMS category
        $items = [
            [
                'name' => 'send-single-sms',
                'display_name' => 'ارسال پیامک تکی',
                'link' => 'sms.single',
                'position' => 1,
            ],
            [
                'name' => 'send-group-sms',
                'display_name' => 'ارسال پیامک گروهی',
                'link' => 'groupsms.create',
                'position' => 2,
            ],
            [
                'name' => 'sms-ai',
                'display_name' => 'ارسال هوشمند با AI',
                'link' => 'admin.sms.ai.index',
                'position' => 3,
            ],
            [
                'name' => 'sms-template',
                'display_name' => 'قالب های پیامکی',
                'link' => 'smsTemplate.index',
                'position' => 4,
            ],
            [
                'name' => 'sms-list',
                'display_name' => 'فهرست پیامک ها',
                'link' => 'groupsms.index',
                'position' => 5,
            ],
            [
                'name' => 'sms-financial',
                'display_name' => 'مدیریت مالی و اعتبار',
                'link' => 'admin.sms.financial.otp.index',
                'position' => 6,
            ],
            [
                'name' => 'sms-reports',
                'display_name' => 'گزارش های پیامک',
                'link' => 'admin.sms.reports.otp.index',
                'position' => 7,
            ],
            [
                'name' => 'sms-settings',
                'display_name' => 'تنظیمات سامانه',
                'link' => 'admin.sms.settings.index',
                'position' => 8,
            ],
        ];

        $roleIds = \Illuminate\Support\Facades\DB::table('roles')
            ->whereIn('name', ['superAdmin', 'admin'])
            ->pluck('id');

        foreach ($items as $item) {
            $permission = Permission::updateOrCreate(
                ['name' => $item['name']],
                [
                    'display_name' => $item['display_name'],
                    'description' => $item['display_name'],
                    'link' => $item['link'],
                    'isMenu' => true,
                    'cat_id' => $smsCategory->id,
                    'position' => $item['position'],
                    'is_active' => true,
                ]
            );

            foreach ($roleIds as $roleId) {
                \Illuminate\Support\Facades\DB::table('permission_role')->updateOrInsert([
                    'role_id' => $roleId,
                    'permission_id' => $permission->id,
                ]);
            }
        }

        MenuService::flush();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert cache flush
        MenuService::flush();
    }
};
