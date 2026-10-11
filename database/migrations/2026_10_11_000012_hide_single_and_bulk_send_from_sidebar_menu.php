<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('permissions')
            ->whereIn('name', ['send-single-sms', 'send-group-sms'])
            ->orWhereIn('link', ['sms.single', 'groupsms.create', 'admin.sms.single.index', 'admin.sms.bulk.index'])
            ->update(['isMenu' => 0]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('permissions')
            ->whereIn('name', ['send-single-sms', 'send-group-sms'])
            ->orWhereIn('link', ['sms.single', 'groupsms.create', 'admin.sms.single.index', 'admin.sms.bulk.index'])
            ->update(['isMenu' => 1]);
    }
};
