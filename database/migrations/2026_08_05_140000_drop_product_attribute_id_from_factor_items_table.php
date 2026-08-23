<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Product details moved off the factor item and into `factor_item_products`.
 *
 * A callcenter invoice now carries ONE factor item per product category, with one
 * factor_item_products row per کالا row the operator added — so a single variant id on the
 * factor item can no longer describe the line. The column never reached production.
 *
 * Guarded both ways so it is a no-op on a database that never had the column.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('factor_items', 'product_attribute_id')) {
            return;
        }

        Schema::table('factor_items', function (Blueprint $table) {
            $table->dropColumn('product_attribute_id');
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('factor_items', 'product_attribute_id')) {
            return;
        }

        Schema::table('factor_items', function (Blueprint $table) {
            $table->integer('product_attribute_id')->nullable()->default(null)->after('service_item_id');
        });
    }
};
