<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * An `about` row already knows which ghorfe owns it (`ghorfe_online_list_id`), but there was
 * no way to say a row belongs to a representation — so /dashboard/about could not be scoped
 * for representation admins. This adds the missing side.
 *
 * unsignedInteger, not foreignId(): representations.id is `int unsigned` (increments), and a
 * bigint FK would be rejected on the type mismatch.
 *
 * Ownership is exclusive: ghorfe_online_list_id XOR representation_id, both null = main site.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('abouts', function (Blueprint $table) {
            $table->unsignedInteger('representation_id')->nullable()->after('ghorfe_online_list_id');

            $table->foreign('representation_id')
                ->references('id')->on('representations')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('abouts', function (Blueprint $table) {
            $table->dropForeign('abouts_representation_id_foreign');
            $table->dropColumn('representation_id');
        });
    }
};
