<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add the «pending_queue» (outgoing queue / unsent draft) status to sms_campaigns.status.
     * Only MySQL keeps a real ENUM type; other drivers store the column as a plain string.
     */
    public function up(): void
    {
        if (!Schema::hasTable('sms_campaigns') || DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement(
            "ALTER TABLE `sms_campaigns` MODIFY `status` ENUM('draft','pending_queue','scheduled','sending','completed','completed_with_errors','failed') NOT NULL DEFAULT 'draft'"
        );
    }

    public function down(): void
    {
        if (!Schema::hasTable('sms_campaigns') || DB::getDriverName() !== 'mysql') {
            return;
        }

        // Rows parked in the queue would violate the narrower enum, so they fall back to draft first.
        DB::table('sms_campaigns')->where('status', 'pending_queue')->update(['status' => 'draft']);

        DB::statement(
            "ALTER TABLE `sms_campaigns` MODIFY `status` ENUM('draft','scheduled','sending','completed','completed_with_errors','failed') NOT NULL DEFAULT 'draft'"
        );
    }
};
