<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * category_permissions already carries a `position` that orders the menu groups in the
     * sidebar. Its sub-menus had no counterpart, so the items inside a group came out in
     * whatever order the query returned. This gives تنظیمات منو the same handle for them.
     */
    public function up(): void
    {
        if (Schema::hasColumn('permissions', 'position')) {
            return;
        }

        Schema::table('permissions', function (Blueprint $table) {
            $table->unsignedInteger('position')->default(0)->after('cat_id');
        });

        // Seed each group's existing order so nothing moves on the first render: the
        // sidebar has always shown sub-menus by ascending id within their group.
        foreach (DB::table('permissions')->select('cat_id')->distinct()->pluck('cat_id') as $categoryId) {
            $ids = DB::table('permissions')
                ->where('cat_id', $categoryId)
                ->orderBy('id')
                ->pluck('id');

            foreach ($ids as $index => $id) {
                DB::table('permissions')->where('id', $id)->update(['position' => $index + 1]);
            }
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('permissions', 'position')) {
            return;
        }

        Schema::table('permissions', function (Blueprint $table) {
            $table->dropColumn('position');
        });
    }
};
