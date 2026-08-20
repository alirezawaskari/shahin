<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * One user = one business.
 *
 * `ghorfe_online_list_user` is the only link between a person and a business the panel has:
 * `ghorfe()` reads it, `isAdmin()` lets a ghorfeAdmin into the panel only while a row exists,
 * and every list scopes itself through it. The whole tenancy model already assumes a single
 * business per user — `AdminAuthService::getGhorfe()` takes `->first()` and silently ignores
 * the rest — but nothing stopped a second row from being written, which would have made the
 * business a user belongs to depend on MyISAM's row order.
 *
 * The rule therefore belongs here and not only in the controller: a unique index on user_id
 * makes a second business impossible whichever code path attaches it.
 *
 * The table is MyISAM (no primary key, no foreign keys) — an index is all it can carry, but
 * it is all this needs.
 */
return new class extends Migration
{
    private const INDEX = 'ghorfe_online_list_user_user_id_unique';

    public function up(): void
    {
        $this->collapseUsersInMoreThanOneBusiness();

        if (! $this->indexExists()) {
            Schema::table('ghorfe_online_list_user', function ($table) {
                $table->unique('user_id', self::INDEX);
            });
        }
    }

    public function down(): void
    {
        // Only the index is reversible: the rows collapsed by up() cannot be reconstructed,
        // and re-adding them would recreate the ambiguity this migration exists to remove.
        if ($this->indexExists()) {
            Schema::table('ghorfe_online_list_user', function ($table) {
                $table->dropUnique(self::INDEX);
            });
        }
    }

    /**
     * Keep the lowest business id for anyone holding several rows.
     *
     * That is the row `getGhorfe()` was already most likely to return, so the business these
     * users see does not change under them.
     */
    private function collapseUsersInMoreThanOneBusiness(): void
    {
        $duplicates = DB::table('ghorfe_online_list_user')
            ->select('user_id', DB::raw('MIN(ghorfe_online_list_id) AS keep_id'))
            ->groupBy('user_id')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicates as $row) {
            DB::table('ghorfe_online_list_user')->where('user_id', $row->user_id)->delete();
            DB::table('ghorfe_online_list_user')->insert([
                'user_id'               => $row->user_id,
                'ghorfe_online_list_id' => $row->keep_id,
            ]);
        }
    }

    private function indexExists(): bool
    {
        return collect(DB::select('SHOW INDEX FROM ghorfe_online_list_user'))
            ->contains(fn ($index) => $index->Key_name === self::INDEX);
    }
};
