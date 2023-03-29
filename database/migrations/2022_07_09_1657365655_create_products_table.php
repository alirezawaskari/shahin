<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {

		$table->integer('id');
		$table->string('name',255);
		$table->string('en_name',255)->nullable()->default(NULL);
		$table->string('slug',110)->nullable()->default(NULL);
		$table->integer('price')->nullable()->default(NULL);
		$table->text('image')->nullable()->default(NULL);
		$table->enum('tariff',['99-1','99-2','98-1','97-1','1400-1','1400-2','1401-1'])->default('99-1');
		$table->integer('is_sale')->default('1');
		$table->integer('isShow')->default('0');
		$table->integer('status')->default('0');
		$table->integer('internal_use')->default('0');
		$table->string('icon' , 255)->nullable()->default(NULL);
		$table->text('description')->nullable()->default(NULL);
		$table->text('review')->nullable()->default(NULL);
		$table->integer('purchase_type')->nullable()->default(NULL);
		$table->integer('pay_type')->nullable()->default(NULL);
		$table->integer('type')->default('2');
		$table->integer('length')->nullable()->default(NULL);
		$table->integer('width')->nullable()->default(NULL);
		$table->integer('height')->nullable()->default(NULL);
		$table->integer('weight')->nullable()->default(NULL);
		$table->bigInteger('reference_price')->nullable()->default(NULL);
        $table->string('message' , 255)->nullable()->default(NULL);
		$table->integer('brand_id')->nullable()->default(NULL);
        $table->foreign('brand_id')->references('id')->on('brands')->onDelete('cascade');
		$table->integer('center_id')->unsigned()->nullable()->default(NULL);
        $table->foreign('center_id')->references('id')->on('centers')->onDelete('cascade');
		$table->integer('root_category_id')->nullable()->default(NULL);
        $table->foreign('root_category_id')->references('id')->on('categories')->onDelete('cascade');

        $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
		$table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('products');
    }
}
