<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateCenterProductTable extends Migration
{
    public function up()
    {
        Schema::create('center_product', function (Blueprint $table) {

		$table->integer('id');
        $table->integer('user_id')->nullable()->default(NULL);
        $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
		$table->integer('product_id')->nullable()->default(NULL);
        $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
		$table->integer('center_id')->nullable()->default(NULL);
        $table->foreign('center_id')->references('id')->on('centers')->onDelete('cascade');
		$table->integer('price')->nullable()->default(NULL);
		$table->integer('stock')->nullable()->default(NULL);
		$table->integer('purchase_type')->nullable()->default(NULL);//
		$table->integer('pay_type')->nullable()->default(NULL);
		$table->integer('tariff_id')->nullable()->default(NULL);
		$table->integer('guarantee')->default('1');

		$table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
		$table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('center_product');
    }
}
