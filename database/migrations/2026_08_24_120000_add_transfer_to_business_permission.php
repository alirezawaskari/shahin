<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const NAME = 'transfer_to_business';

    public function up(): void
    {
        $categoryId = DB::table('category_permissions')
            ->where('name', 'مغازه دیجیتال')
            ->where('is_active', 1)
            ->whereNull('deleted_at')
            ->value('id');

        if (! $categoryId) {
            throw new RuntimeException('Permission category «مغازه دیجیتال» was not found.');
        }

        $attributes = [
            'name' => self::NAME,
            'display_name' => 'انتقال به کسب و کار',
            'description' => 'انتقال تکی و گروهی رکوردهای مجاز به یک کسب و کار فعال دیگر',
            'cat_id' => $categoryId,
            'isMenu' => 0,
            'link' => null,
            'is_active' => 1,
            'updated_at' => now(),
        ];

        $permission = DB::table('permissions')->where('name', self::NAME)->first();
        if ($permission) {
            DB::table('permissions')->where('id', $permission->id)->update($attributes);

            return;
        }

        DB::table('permissions')->insert($attributes + ['created_at' => now()]);
    }

    public function down(): void
    {
        $permission = DB::table('permissions')->where('name', self::NAME)->first();
        if (! $permission) {
            return;
        }

        DB::transaction(function () use ($permission) {
            DB::table('permission_role')->where('permission_id', $permission->id)->delete();
            DB::table('permission_user')->where('permission_id', $permission->id)->delete();
            DB::table('permissions')->where('id', $permission->id)->delete();
        });
    }
};
