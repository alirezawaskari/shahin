<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateCategoryProductTable extends Migration
{
    public function up()
    {
        Schema::create('category_product', function (Blueprint $table) {

		$table->integer('id');
		$table->integer('product_id');
        $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
		$table->integer('category_id');
        $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');

        $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
		$table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('category_product');
    }
}
