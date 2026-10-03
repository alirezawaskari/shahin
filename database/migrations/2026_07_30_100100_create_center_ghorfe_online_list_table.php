<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The missing "a business has many branches" link.
 *
 * `centers` (شعبه/مستاجرین و شرکای تجاری) already existed and `ghorfe_online_lists` (کسب و کار) already
 * existed, but nothing joined them — a center only knew its creator representation
 * (creator_representation_id) or its polymorphic owner, neither of which answers
 * "which branches belong to this digital store?", the question the about-us branch list
 * and the branch frontend both ask.
 *
 * Many-to-many rather than a center_id column on the business: a business has several
 * branches, and a shared/partner center can legitimately serve more than one business.
 *
 * Column types are matched to the referenced PKs, not to Laravel defaults:
 * ghorfe_online_lists.id is `bigint unsigned`, centers.id is `int unsigned`. A foreignId()
 * bigint on the center side would be rejected on the type mismatch.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('center_ghorfe_online_list', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ghorfe_online_list_id');
            $table->unsignedInteger('center_id');
            // display order of the branch cards on the about-us page
            $table->unsignedSmallInteger('sort')->nullable();

            // one row per pair — re-syncing must not be able to duplicate a branch
            $table->unique(['ghorfe_online_list_id', 'center_id'], 'center_ghorfe_unique');

            $table->foreign('ghorfe_online_list_id')
                ->references('id')->on('ghorfe_online_lists')
                ->onDelete('cascade');
            $table->foreign('center_id')
                ->references('id')->on('centers')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('center_ghorfe_online_list');
    }
};
