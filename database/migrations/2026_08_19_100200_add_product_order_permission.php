<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * The admin sidebar is built from `permissions` rows (see MenuService / AdminMenuComposer),
 * so a new section only becomes reachable once it has a row here. This registers
 * "مدیریت سفارشات کالا" under the محصولات category (cat_id 10).
 *
 * superAdmin / admin see every menu entry, so no role wiring is needed for them; other
 * roles get it granted from the roles screen like any other permission.
 */
return new class extends Migration
{
    private const NAME = 'product-order';

    public function up(): void
    {
        if (DB::table('permissions')->where('name', self::NAME)->exists()) {
            return;
        }

        DB::table('permissions')->insert([
            'name' => self::NAME,
            'display_name' => 'مدیریت سفارشات کالا',
            'description' => 'مشاهده و مدیریت سفارشات کالا، آدرس ارسال، هزینه ارسال و ارسال سفارش',
            'link' => 'productOrder.index',
            'isMenu' => 1,
            'is_active' => 1,
            'cat_id' => 10,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
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
    }
};
