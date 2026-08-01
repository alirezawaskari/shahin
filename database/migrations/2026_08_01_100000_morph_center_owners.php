<?php

use App\Models\GhorfeOnlineList;
use App\Models\Representation;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Store every many-to-many Center association in the existing polymorphic
 * `centerables` pivot instead of keeping one pivot table per owner model.
 *
 * Category and Service already use this table. Representation and GhorfeOnlineList can
 * use it too; `sort` is nullable because only business branch cards currently need it.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('centerables', function (Blueprint $table) {
            // GhorfeOnlineList has a bigint primary key; Category/Service/Representation
            // ids also fit safely in this wider polymorphic id.
            $table->unsignedBigInteger('centerable_id')->change();
            $table->unsignedSmallInteger('sort')->nullable()->after('centerable_type');
        });

        $representationType = (new Representation())->getMorphClass();
        $ghorfeType = (new GhorfeOnlineList())->getMorphClass();

        if (Schema::hasTable('center_representation')) {
            DB::table('center_representation')->orderBy('center_id')->each(function ($row) use ($representationType) {
                DB::table('centerables')->updateOrInsert([
                    'center_id'       => $row->center_id,
                    'centerable_type' => $representationType,
                    'centerable_id'   => $row->representation_id,
                ], [
                    'sort' => null,
                ]);
            });
        }

        if (Schema::hasTable('center_ghorfe_online_list')) {
            DB::table('center_ghorfe_online_list')->orderBy('id')->each(function ($row) use ($ghorfeType) {
                DB::table('centerables')->updateOrInsert([
                    'center_id'       => $row->center_id,
                    'centerable_type' => $ghorfeType,
                    'centerable_id'   => $row->ghorfe_online_list_id,
                ], [
                    'sort' => $row->sort,
                ]);
            });
        }

        Schema::table('centerables', function (Blueprint $table) {
            $table->unique(
                ['center_id', 'centerable_type', 'centerable_id'],
                'centerables_owner_unique'
            );
        });

        Schema::dropIfExists('center_representation');
        Schema::dropIfExists('center_ghorfe_online_list');
    }

    public function down(): void
    {
        Schema::create('center_representation', function (Blueprint $table) {
            // These reproduce the types of the legacy local table.
            $table->unsignedBigInteger('center_id');
            $table->unsignedBigInteger('representation_id');
        });

        Schema::create('center_ghorfe_online_list', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ghorfe_online_list_id');
            $table->unsignedInteger('center_id');
            $table->unsignedSmallInteger('sort')->nullable();
            $table->unique(['ghorfe_online_list_id', 'center_id'], 'center_ghorfe_unique');
        });

        $representationType = (new Representation())->getMorphClass();
        $ghorfeType = (new GhorfeOnlineList())->getMorphClass();

        DB::table('centerables')->where('centerable_type', $representationType)
            ->orderBy('id')->each(function ($row) {
                DB::table('center_representation')->insert([
                    'center_id'        => $row->center_id,
                    'representation_id' => $row->centerable_id,
                ]);
            });

        DB::table('centerables')->where('centerable_type', $ghorfeType)
            ->orderBy('id')->each(function ($row) {
                DB::table('center_ghorfe_online_list')->insert([
                    'ghorfe_online_list_id' => $row->centerable_id,
                    'center_id'             => $row->center_id,
                    'sort'                  => $row->sort,
                ]);
            });

        DB::table('centerables')->whereIn('centerable_type', [$representationType, $ghorfeType])->delete();

        Schema::table('centerables', function (Blueprint $table) {
            $table->dropUnique('centerables_owner_unique');
            $table->dropColumn('sort');
            $table->unsignedInteger('centerable_id')->change();
        });
    }
};
