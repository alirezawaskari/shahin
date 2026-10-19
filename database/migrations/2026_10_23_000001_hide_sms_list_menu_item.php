<?php

use App\Models\Permission;
use App\Services\V1\Admin\MenuService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * «فهرست پیامک ها» (sms-list) is retired from the sidebar: its receiving/listing features now live in
     * the SMS reports pages. The permission and route stay; only the menu flag is switched off.
     */
    public function up(): void
    {
        if (!Schema::hasTable('permissions')) {
            return;
        }

        Permission::where('name', 'sms-list')->update(['isMenu' => false]);
        MenuService::flush();
    }

    public function down(): void
    {
        if (!Schema::hasTable('permissions')) {
            return;
        }

        Permission::where('name', 'sms-list')->update(['isMenu' => true]);
        MenuService::flush();
    }
};
