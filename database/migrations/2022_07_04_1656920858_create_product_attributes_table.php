<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateProductAttributesTable extends Migration
{
    public function up()
    {
        Schema::create('product_attributes', function (Blueprint $table) {

		$table->integer('id');
		$table->integer('attribute_id')->nullable()->default(NULL);
        $table->foreign('attribute_id')->references('id')->on('attributes')->onDelete('cascade');
		$table->integer('attribute_item_id')->nullable()->default(NULL);
        $table->foreign('attribute_item_id')->references('id')->on('attribute_items')->onDelete('cascade');
		$table->string('value',120)->nullable()->default(NULL);
		$table->integer('product_id');
        $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
        $table->morphs('productattributeable', 'product_attributeable_index');//Center Models || StoreRoom Models
		$table->bigInteger('price')->nullable()->default(NULL);
		$table->integer('initial_stock')->nullable()->default(NULL);
		$table->integer('stock')->nullable()->default(NULL);
		$table->integer('guarantee')->nullable()->default(NULL);
		$table->integer('posted_by')->nullable()->default(NULL);
		$table->integer('is_show')->default('0');
		$table->integer('submission_time')->nullable()->default(NULL);
		$table->string('seller_code',110)->nullable()->default(NULL);

		$table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
		$table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('product_attributes');
    }
}
