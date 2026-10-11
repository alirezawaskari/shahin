<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Renames the unified SMS category_permissions row from "پیامک" to
     * "مدیریت پیامک (SMS) CRM" so the menu group shows the CRM label.
     */
    public function up(): void
    {
        DB::table('category_permissions')
            ->where('name', 'پیامک')
            ->update(['name' => 'مدیریت پیامک (SMS) CRM']);
    }

    /**
     * Reverts the label back to "پیامک".
     */
    public function down(): void
    {
        DB::table('category_permissions')
            ->where('name', 'مدیریت پیامک (SMS) CRM')
            ->update(['name' => 'پیامک']);
    }
};
