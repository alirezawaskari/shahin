<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Rename the `center_owners` morph from `centerable` to `owner`.
 *
 * `centerable` was already taken, with the opposite meaning: `centers.centerable_type` and
 * the `centerables` pivot say what a center OFFERS (Category | Service), while the same
 * word on this pivot said who OWNS it (Representation | GhorfeOnlineList). Both appeared on
 * the Center model at once, so `centerable_type` could not be read without first knowing
 * which table it came from — see FilterServiceProvider, which tests
 * `$user->center->centerable_type == Service::class`.
 *
 * Only the two morph columns move; `center_id`, `sort` and the unique index are untouched
 * (the index was already named `center_owners_owner_unique`). MySQL's native RENAME COLUMN
 * carries indexes across, so nothing has to be rebuilt.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('center_owners', function (Blueprint $table) {
            $table->renameColumn('centerable_id', 'owner_id');
            $table->renameColumn('centerable_type', 'owner_type');
        });
    }

    public function down(): void
    {
        Schema::table('center_owners', function (Blueprint $table) {
            $table->renameColumn('owner_id', 'centerable_id');
            $table->renameColumn('owner_type', 'centerable_type');
        });
    }
};
