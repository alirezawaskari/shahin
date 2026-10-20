<?php

use App\Services\V1\Admin\MenuService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Configure the CRM SMS Module in the sidebar menu:
     *
     * Category: "مدیریت پیامک (SMS) CRM" (icon: ti-comments-smiley, position: 28)
     * Subitems in exact required order:
     *   1. تنظیمات سامانه -> admin.sms.settings.index
     *   2. گزارش های پیامک -> admin.sms.reports.otp.index
     *   3. پیشخوان پیامک -> admin.sms.dashboard.index
     *   4. ارسال پیامک تکی -> sms.single
     *   5. ارسال پیامک گروهی -> groupsms.create
     *   6. ارسال هوشمند با AI -> admin.sms.ai.index
     *   7. قالب های پیامکی -> smsTemplate.index
     *   8. مدیریت مالی و اعتبار -> admin.sms.financial.otp.index
     *
     * Legacy / sub-actions excluded from sidebar:
     *   - sms-list (فهرست پیامک ها -> groupsms.index): old version of reports, excluded
     *   - sms-template-create (ایجاد قالب پیامکی): accessible inside template management
     */
    public function up(): void
    {
        $categoryName = 'مدیریت پیامک (SMS) CRM';

        // 1. Locate or rename existing category to "مدیریت پیامک (SMS) CRM"
        $smsCat = DB::table('category_permissions')
            ->where('name', $categoryName)
            ->orWhere('name', 'پیامک')
            ->first();

        if ($smsCat) {
            DB::table('category_permissions')->where('id', $smsCat->id)->update([
                'name' => $categoryName,
                'icon' => 'ti-comments-smiley',
                'position' => 28,
                'is_active' => 1,
                'updated_at' => now(),
            ]);
            $smsCatId = $smsCat->id;
        } else {
            $smsCatId = DB::table('category_permissions')->insertGetId([
                'name' => $categoryName,
                'icon' => 'ti-comments-smiley',
                'position' => 28,
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Deactivate standalone legacy template category so templates live under the unified CRM category
        $templateCatId = DB::table('category_permissions')->where('name', 'قالب های پیامکی')->value('id');
        if ($templateCatId && $templateCatId !== $smsCatId) {
            DB::table('category_permissions')->where('id', $templateCatId)->update([
                'is_active' => 0,
            ]);
        }

        // 2. Visible subitems in required order: Settings first, Reports second
        $smsItems = [
            [
                'name' => 'sms-settings',
                'display_name' => 'تنظیمات سامانه',
                'link' => 'admin.sms.settings.index',
                'position' => 1,
            ],
            [
                'name' => 'sms-reports',
                'display_name' => 'گزارش های پیامک',
                'link' => 'admin.sms.reports.otp.index',
                'position' => 2,
            ],
            [
                'name' => 'sms-dashboard',
                'display_name' => 'پیشخوان پیامک',
                'link' => 'admin.sms.dashboard.index',
                'position' => 3,
            ],
            [
                'name' => 'send-single-sms',
                'display_name' => 'ارسال پیامک تکی',
                'link' => 'sms.single',
                'position' => 4,
            ],
            [
                'name' => 'send-group-sms',
                'display_name' => 'ارسال پیامک گروهی',
                'link' => 'groupsms.create',
                'position' => 5,
            ],
            [
                'name' => 'sms-ai',
                'display_name' => 'ارسال هوشمند با AI',
                'link' => 'admin.sms.ai.index',
                'position' => 6,
            ],
            [
                'name' => 'sms-template',
                'display_name' => 'قالب های پیامکی',
                'link' => 'smsTemplate.index',
                'position' => 7,
            ],
            [
                'name' => 'sms-financial',
                'display_name' => 'مدیریت مالی و اعتبار',
                'link' => 'admin.sms.financial.otp.index',
                'position' => 8,
            ],
        ];

        // Explicitly exclude legacy / action permissions from sidebar menu
        DB::table('permissions')->whereIn('name', ['sms-list', 'sms-template-create'])->update([
            'isMenu' => 0,
            'cat_id' => $smsCatId,
        ]);

        foreach ($smsItems as $item) {
            $existing = DB::table('permissions')->where('name', $item['name'])->first();
            if ($existing) {
                DB::table('permissions')->where('id', $existing->id)->update([
                    'display_name' => $item['display_name'],
                    'link' => $item['link'],
                    'cat_id' => $smsCatId,
                    'position' => $item['position'],
                    'isMenu' => 1,
                    'is_active' => 1,
                    'updated_at' => now(),
                ]);
            } else {
                DB::table('permissions')->insert([
                    'name' => $item['name'],
                    'display_name' => $item['display_name'],
                    'description' => $item['display_name'],
                    'link' => $item['link'],
                    'cat_id' => $smsCatId,
                    'position' => $item['position'],
                    'isMenu' => 1,
                    'is_active' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // 3. Attach category to packages so businesses can see it
        if (DB::getSchemaBuilder()->hasTable('category_permission_shahin_package')) {
            $packageIds = DB::table('shahin_packages')->pluck('id');
            foreach ($packageIds as $pkgId) {
                $exists = DB::table('category_permission_shahin_package')
                    ->where('shahin_package_id', $pkgId)
                    ->where('category_permission_id', $smsCatId)
                    ->exists();

                if (! $exists) {
                    DB::table('category_permission_shahin_package')->insert([
                        'shahin_package_id' => $pkgId,
                        'category_permission_id' => $smsCatId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        // 4. Attach all permissions to superadmin, admin and ghorfeAdmin
        $targetNames = array_merge(array_column($smsItems, 'name'), ['sms-list', 'sms-template-create']);
        $permIds = DB::table('permissions')->whereIn('name', $targetNames)->pluck('id');

        $roles = DB::table('roles')->whereIn('name', ['superadmin', 'ghorfeAdmin', 'admin'])->get();
        foreach ($roles as $role) {
            $held = DB::table('permission_role')->where('role_id', $role->id)->pluck('permission_id')->all();
            $missing = $permIds->diff($held);
            foreach ($missing as $pId) {
                DB::table('permission_role')->insert([
                    'role_id' => $role->id,
                    'permission_id' => $pId,
                ]);
            }
        }

        // 5. Invalidate menu caches across all users
        MenuService::flush();
    }

    public function down(): void
    {
        MenuService::flush();
    }
};
