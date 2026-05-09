<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('special_sale_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('special_sale_group_id')
                ->constrained('special_sale_groups')
                ->cascadeOnDelete();

            $table->unsignedInteger('product_id');
            $table->foreign('product_id')
                ->references('id')
                ->on('products')
                ->cascadeOnDelete();

            $table->decimal('percentage', 5, 2);
            $table->unsignedInteger('priority')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['special_sale_group_id', 'product_id'], 'group_product_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('special_sale_items');
    }
};
