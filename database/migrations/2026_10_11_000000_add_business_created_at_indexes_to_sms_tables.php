<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Supports per-business, time-ordered lookups (message logs, transaction history,
     * and report ranges) without scanning the whole table.
     */
    public function up(): void
    {
        Schema::table('sms_messages', function (Blueprint $table) {
            $table->index(['ghorfe_online_list_id', 'created_at']);
        });

        Schema::table('sms_transactions', function (Blueprint $table) {
            $table->index(['ghorfe_online_list_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sms_messages', function (Blueprint $table) {
            $table->dropIndex(['ghorfe_online_list_id', 'created_at']);
        });

        Schema::table('sms_transactions', function (Blueprint $table) {
            $table->dropIndex(['ghorfe_online_list_id', 'created_at']);
        });
    }
};
