<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Which BUSINESS an employee belongs to, as its own column.
 *
 * The "درباره ما" staff list used to be derived from the branch (employees.center_id ->
 * center_owners -> business). That made the two questions one: an employee could not belong
 * to a business without being posted to one of its branches, and moving them between
 * branches silently moved them between businesses. They are separate facts, so this is a
 * separate column.
 *
 * Backfilled from the branch the employee already works at, so nothing that was on a page
 * falls off it.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->unsignedBigInteger('ghorfe_online_list_id')->nullable()->after('center_id');

            $table->foreign('ghorfe_online_list_id')
                ->references('id')->on('ghorfe_online_lists')
                ->onDelete('set null');
        });

        // center_owners holds the business of each branch; a branch shared by two businesses
        // resolves to the lowest id, which is arbitrary but deterministic — the admin can
        // correct it on the employee's own page.
        DB::table('employees')
            ->whereNotNull('center_id')
            ->update([
                'ghorfe_online_list_id' => DB::raw(
                    '(select min(owner_id) from center_owners'
                    . " where center_owners.center_id = employees.center_id"
                    . " and center_owners.owner_type = 'App\\\\Models\\\\GhorfeOnlineList')"
                ),
            ]);
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropForeign('employees_ghorfe_online_list_id_foreign');
            $table->dropColumn('ghorfe_online_list_id');
        });
    }
};
