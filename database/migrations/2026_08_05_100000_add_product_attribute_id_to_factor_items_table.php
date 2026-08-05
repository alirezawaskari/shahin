<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The callcenter send-invoice form now picks a concrete product variant (Category ->
 * Product -> Variant) so it can read that variant's registration fee. The factor item
 * itself stays Category-based, exactly as before, so nothing downstream has to change;
 * this column just records WHICH variant supplied the price.
 *
 * No foreign key: `product_attributes` is MyISAM, which cannot be the target of one
 * (MySQL errors 1824). `request_items.product_attribute_id` is in the same position.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('factor_items', 'product_attribute_id')) {
            return;
        }

        Schema::table('factor_items', function (Blueprint $table) {
            $table->integer('product_attribute_id')->nullable()->default(null)->after('service_item_id');
        });
    }

    public function down(): void
    {
        Schema::table('factor_items', function (Blueprint $table) {
            $table->dropColumn('product_attribute_id');
        });
    }
};
