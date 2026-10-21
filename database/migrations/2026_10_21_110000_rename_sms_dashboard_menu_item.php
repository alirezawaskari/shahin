<?php

use App\Services\V1\Admin\MenuService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * The sidebar is read from the `permissions` table, so changing the label inside an already-run
 * migration never reaches existing databases. This renames the live row.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('permissions')->where('name', 'sms-dashboard')->update([
            'display_name' => 'پیشخوان پیامک',
            'description' => 'پیشخوان پیامک',
            'updated_at' => now(),
        ]);

        MenuService::flush();
    }

    public function down(): void
    {
        DB::table('permissions')->where('name', 'sms-dashboard')->update([
            'display_name' => 'مدیریت ارسال و دریافت',
            'description' => 'مدیریت ارسال و دریافت',
            'updated_at' => now(),
        ]);

        MenuService::flush();
    }
};
