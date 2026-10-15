<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Advertising credit belongs to the business' advertising panel, not to an
 * individual sender line. Collapse the per-(business, line) rows into one row
 * per business, summing the existing balances so no credit is lost.
 *
 * sms_transactions keeps its sender_number_id: it still records which line
 * sent a message, it just no longer decides which wallet is debited.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function () {
            $businessIds = DB::table('advertising_balances')
                ->select('ghorfe_online_list_id')
                ->groupBy('ghorfe_online_list_id')
                ->havingRaw('COUNT(*) > 1')
                ->pluck('ghorfe_online_list_id');

            foreach ($businessIds as $businessId) {
                $rows = DB::table('advertising_balances')
                    ->where('ghorfe_online_list_id', $businessId)
                    ->orderBy('id')
                    ->get();

                $keep = $rows->first();
                $total = $rows->sum(fn ($row) => (float) $row->balance);

                DB::table('advertising_balances')
                    ->where('id', $keep->id)
                    ->update(['balance' => $total]);

                DB::table('advertising_balances')
                    ->where('ghorfe_online_list_id', $businessId)
                    ->where('id', '!=', $keep->id)
                    ->delete();
            }
        });

        Schema::table('advertising_balances', function (Blueprint $table) {
            // Added first: MySQL needs an index on the business FK column
            // before the old composite unique index can be dropped.
            $table->unique('ghorfe_online_list_id', 'advertising_balances_business_unique');
        });

        Schema::table('advertising_balances', function (Blueprint $table) {
            $table->dropUnique('advertising_balances_business_sender_unique');
            $table->dropForeign(['sender_number_id']);
            $table->dropColumn('sender_number_id');
        });
    }

    /**
     * Structure only: the merged balance cannot be split back across lines.
     * The surviving row gets a NULL sender_number_id.
     */
    public function down(): void
    {
        Schema::table('advertising_balances', function (Blueprint $table) {
            $table->foreignId('sender_number_id')->nullable()->constrained('sender_numbers')->cascadeOnDelete();
            $table->unique(['ghorfe_online_list_id', 'sender_number_id'], 'advertising_balances_business_sender_unique');
        });

        Schema::table('advertising_balances', function (Blueprint $table) {
            $table->dropUnique('advertising_balances_business_unique');
        });
    }
};
