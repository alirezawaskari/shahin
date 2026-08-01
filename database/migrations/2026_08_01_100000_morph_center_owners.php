<?php

use App\Models\GhorfeOnlineList;
use App\Models\Representation;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Rename the existing center_representation pivot to center_owners and turn it into the
 * shared polymorphic pivot for Representation and GhorfeOnlineList. The unrelated
 * `centerables` table remains devoted to Category/Service associations.
 *
 * Before: center_id + representation_id
 * After:  center_id + centerable_type + centerable_id + sort
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::rename('center_representation', 'center_owners');

        Schema::table('center_owners', function (Blueprint $table) {
            $table->renameColumn('representation_id', 'centerable_id');
        });

        Schema::table('center_owners', function (Blueprint $table) {
            // Center ids are int unsigned; the polymorphic id must also accommodate the
            // bigint key used by GhorfeOnlineList.
            $table->unsignedInteger('center_id')->change();
            $table->unsignedBigInteger('centerable_id')->change();
            $table->string('centerable_type', 191)->nullable()->after('centerable_id');
            $table->unsignedSmallInteger('sort')->nullable()->after('centerable_type');
        });

        $representationType = (new Representation())->getMorphClass();
        $ghorfeType = (new GhorfeOnlineList())->getMorphClass();

        DB::table('center_owners')->update([
            'centerable_type' => $representationType,
        ]);

        if (Schema::hasTable('center_ghorfe_online_list')) {
            DB::table('center_ghorfe_online_list')->orderBy('id')->each(function ($row) use ($ghorfeType) {
                DB::table('center_owners')->updateOrInsert([
                    'center_id'       => $row->center_id,
                    'centerable_type' => $ghorfeType,
                    'centerable_id'   => $row->ghorfe_online_list_id,
                ], [
                    'sort' => $row->sort,
                ]);
            });
        }

        Schema::table('center_owners', function (Blueprint $table) {
            $table->string('centerable_type', 191)->nullable(false)->change();
            $table->unique(
                ['center_id', 'centerable_type', 'centerable_id'],
                'center_owners_owner_unique'
            );
        });

        Schema::dropIfExists('center_ghorfe_online_list');
    }

    public function down(): void
    {
        Schema::create('center_ghorfe_online_list', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ghorfe_online_list_id');
            $table->unsignedInteger('center_id');
            $table->unsignedSmallInteger('sort')->nullable();
            $table->unique(['ghorfe_online_list_id', 'center_id'], 'center_ghorfe_unique');
        });

        $representationType = (new Representation())->getMorphClass();
        $ghorfeType = (new GhorfeOnlineList())->getMorphClass();

        DB::table('center_owners')->where('centerable_type', $ghorfeType)
            ->orderBy('center_id')->each(function ($row) {
                DB::table('center_ghorfe_online_list')->insert([
                    'ghorfe_online_list_id' => $row->centerable_id,
                    'center_id'             => $row->center_id,
                    'sort'                  => $row->sort,
                ]);
            });

        DB::table('center_owners')->where('centerable_type', $ghorfeType)->delete();

        // The legacy table can represent only Representation rows. Refuse a lossy rollback
        // if another owner type is introduced in the future.
        if (DB::table('center_owners')->where('centerable_type', '!=', $representationType)->exists()) {
            throw new RuntimeException('Cannot roll back center owners with unsupported morph types.');
        }

        Schema::table('center_owners', function (Blueprint $table) {
            $table->dropUnique('center_owners_owner_unique');
            $table->dropColumn(['centerable_type', 'sort']);
        });

        Schema::table('center_owners', function (Blueprint $table) {
            $table->renameColumn('centerable_id', 'representation_id');
        });

        Schema::table('center_owners', function (Blueprint $table) {
            $table->unsignedBigInteger('center_id')->change();
            $table->unsignedBigInteger('representation_id')->change();
        });

        Schema::rename('center_owners', 'center_representation');
    }
};
