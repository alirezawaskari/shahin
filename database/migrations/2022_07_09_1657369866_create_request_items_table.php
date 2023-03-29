<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateRequestItemsTable extends Migration
{
    public function up()
    {
        Schema::create('request_items', function (Blueprint $table) {

		$table->integer('id');
		$table->integer('product_id')->nullable()->default(NULL);
        $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
		$table->integer('request_id')->nullable()->default(NULL);
        $table->foreign('request_id')->references('id')->on('requests')->onDelete('cascade');
		$table->integer('number')->nullable()->default(NULL);
		$table->integer('product_attribute_id')->nullable()->default(NULL);
        $table->foreign('product_attribute_id')->references('id')->on('product_attributes')->onDelete('cascade');

        $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
		$table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('request_items');
    }
}
