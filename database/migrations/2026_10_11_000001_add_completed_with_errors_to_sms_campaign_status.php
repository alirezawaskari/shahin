<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add a status for campaigns that stopped on balance exhaustion with recipients unsent.
     */
    public function up(): void
    {
        Schema::table('sms_campaigns', function (Blueprint $table) {
            $table->enum('status', ['draft', 'scheduled', 'sending', 'completed', 'completed_with_errors', 'failed'])
                ->default('draft')
                ->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('sms_campaigns')
            ->where('status', 'completed_with_errors')
            ->update(['status' => 'completed']);

        Schema::table('sms_campaigns', function (Blueprint $table) {
            $table->enum('status', ['draft', 'scheduled', 'sending', 'completed', 'failed'])
                ->default('draft')
                ->change();
        });
    }
};
