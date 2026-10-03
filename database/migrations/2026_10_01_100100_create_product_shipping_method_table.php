<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Which of the business's shipping methods (shipping_methods, one list per business) are
 * offered for a product. Filled from the product form; read by the product API for the
 * single-product page.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('product_shipping_method')) {
            return;
        }

        Schema::create('product_shipping_method', function (Blueprint $table) {
            $table->id();
            // products.id is a signed int
            $table->integer('product_id');
            $table->unsignedBigInteger('shipping_method_id');

            $table->unique(['product_id', 'shipping_method_id']);
            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
            // shipping_methods is created by the product API's migrations, not this repo's
            if (Schema::hasTable('shipping_methods')) {
                $table->foreign('shipping_method_id')->references('id')->on('shipping_methods')->cascadeOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_shipping_method');
    }
};
