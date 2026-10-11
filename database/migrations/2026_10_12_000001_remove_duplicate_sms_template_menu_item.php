<?php

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
        if (!Schema::hasTable('permissions')) {
            return;
        }

        Permission::where('name', 'sms-template')->update([
            'isMenu' => false,
        ]);

        if (class_exists(MenuService::class)) {
            MenuService::flush();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('permissions')) {
            return;
        }

        Permission::where('name', 'sms-template')->update([
            'isMenu' => true,
        ]);

        if (class_exists(MenuService::class)) {
            MenuService::flush();
        }
    }
};
