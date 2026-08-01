<?php

use App\Models\GhorfeOnlineList;
use App\Models\Representation;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Ownership of a "درباره ما" page becomes polymorphic.
 *
 * A page belongs to exactly ONE owner — a business, a representation, or (no owner at all)
 * the main lalecard site. That was modelled as two mutually exclusive nullable FK columns,
 * which only a comment and scopeFilter kept honest, and which needed a schema change every
 * time a new kind of owner appeared.
 *
 * Every other kind of tenant-owned content here is already polymorphic — sliders
 * (sliderable), articles (articleable), tickets (ticketable), contacts, cards — and both
 * this project and the API already carried the matching dead code:
 * GhorfeOnlineList::abouts() was a morphMany on 'aboutable' and About::aboutable() a
 * morphTo, against columns that did not exist. This finishes that.
 *
 * nullableMorphs, not morphs: the main-site page genuinely has no owner, and NULL/NULL is
 * the honest representation of that (the two dropped columns said the same thing with two
 * nulls instead of one).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('abouts', function (Blueprint $table) {
            $table->nullableMorphs('aboutable');
        });

        // Backfill before the old columns go: a business page, then a representation page.
        // Rows with neither are the main site and are correctly left NULL/NULL.
        if (Schema::hasColumn('abouts', 'ghorfe_online_list_id')) {
            DB::table('abouts')->whereNotNull('ghorfe_online_list_id')->update([
                'aboutable_type' => GhorfeOnlineList::class,
                'aboutable_id'   => DB::raw('ghorfe_online_list_id'),
            ]);
        }

        if (Schema::hasColumn('abouts', 'representation_id')) {
            DB::table('abouts')->whereNotNull('representation_id')->update([
                'aboutable_type' => Representation::class,
                'aboutable_id'   => DB::raw('representation_id'),
            ]);

            Schema::table('abouts', function (Blueprint $table) {
                $table->dropForeign('abouts_representation_id_foreign');
                $table->dropColumn('representation_id');
            });
        }

        if (Schema::hasColumn('abouts', 'ghorfe_online_list_id')) {
            Schema::table('abouts', function (Blueprint $table) {
                $table->dropColumn('ghorfe_online_list_id');
            });
        }
    }

    public function down(): void
    {
        Schema::table('abouts', function (Blueprint $table) {
            // original types: abouts.ghorfe_online_list_id was a plain signed int (no FK),
            // representation_id an unsignedInteger FK matching representations.id
            $table->integer('ghorfe_online_list_id')->nullable()->after('id');
            $table->unsignedInteger('representation_id')->nullable()->after('ghorfe_online_list_id');

            $table->foreign('representation_id')
                ->references('id')->on('representations')
                ->onDelete('cascade');
        });

        DB::table('abouts')->where('aboutable_type', GhorfeOnlineList::class)
            ->update(['ghorfe_online_list_id' => DB::raw('aboutable_id')]);

        DB::table('abouts')->where('aboutable_type', Representation::class)
            ->update(['representation_id' => DB::raw('aboutable_id')]);

        Schema::table('abouts', function (Blueprint $table) {
            $table->dropMorphs('aboutable');
        });
    }
};
