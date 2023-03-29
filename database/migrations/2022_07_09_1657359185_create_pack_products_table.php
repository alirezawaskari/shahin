<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreatePackProductsTable extends Migration
{
    public function up()
    {
        Schema::create('pack_products', function (Blueprint $table) {

		$table->integer('id');
		$table->integer('pack_id')->nullable()->default(NULL);
        $table->foreign('pack_id')->references('id')->on('packs')->onDelete('cascade');
		$table->integer('product_id')->nullable()->default(NULL);
        $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
		$table->integer('guarantee')->default('1');

		$table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
		$table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pack_products');
    }
}
