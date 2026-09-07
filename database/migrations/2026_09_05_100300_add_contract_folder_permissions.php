<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * name => [display_name, description, isMenu, link, position]
     *
     * Only the folder list belongs in the sidebar; the rest are action permissions
     * guarding the routes, exactly like the existing contract.* entries.
     */
    private const PERMISSIONS = [
        'contract_folder_list' => ['پوشه های قرارداد', 'مشاهده فهرست پوشه های بانک قرارداد', 1, 'contractFolder.index', 4],
        'contract_folder_create' => ['ایجاد پوشه قرارداد', 'ایجاد پوشه جدید در بانک قرارداد', 0, null, 5],
        'contract_folder_edit' => ['ویرایش پوشه قرارداد', 'ویرایش پوشه های بانک قرارداد', 0, null, 6],
        'contract_folder_delete' => ['حذف پوشه قرارداد', 'حذف پوشه های بانک قرارداد', 0, null, 7],
        'contract_usage_manage' => ['اتصال قرارداد', 'اتصال قرارداد به خدمت، نقش و محل استفاده', 0, null, 8],
        'contract_export' => ['خروجی قرارداد', 'خروجی و دانلود فایل قرارداد', 0, null, 9],
    ];

    public function up(): void
    {
        $categoryId = DB::table('category_permissions')
            ->where('name', 'قراردادها')
            ->where('is_active', 1)
            ->whereNull('deleted_at')
            ->value('id');

        if (! $categoryId) {
            throw new RuntimeException('Permission category «قراردادها» was not found.');
        }

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

                continue;
            }

            DB::table('permissions')->insert($attributes + ['created_at' => now()]);
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
