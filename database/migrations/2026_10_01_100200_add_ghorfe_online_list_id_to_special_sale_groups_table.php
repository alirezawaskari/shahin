<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * «شگفت‌انگیز» groups were global. The panel now creates one group per «افزودن به
 * شگفت‌انگیز» submission of a business (its end time is the group's ends_at), so the group
 * records which business made it — that is what scopes the panel's list. Nullable: the
 * product API reads groups by product ownership and keeps working for rows without it.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('special_sale_groups', function (Blueprint $table) {
            if (!Schema::hasColumn('special_sale_groups', 'ghorfe_online_list_id')) {
                $table->unsignedBigInteger('ghorfe_online_list_id')->nullable()->after('id')->index();
            }
        });
    }

    public function down(): void
    {
        Schema::table('special_sale_groups', function (Blueprint $table) {
            $table->dropIndex(['ghorfe_online_list_id']);
            $table->dropColumn('ghorfe_online_list_id');
        });
    }
};
