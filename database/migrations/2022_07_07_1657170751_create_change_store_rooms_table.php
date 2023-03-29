<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateChangeStoreRoomsTable extends Migration
{
    public function up()
    {
        Schema::create('change_store_rooms', function (Blueprint $table) {

		$table->integer('id');
		$table->integer('product_id')->nullable()->default(NULL);
        $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
        $table->morphs('changestoreroomable', 'change_store_roomable_index');//StoreRoom Models for material || ProductAttribute Models for products
		$table->integer('number')->nullable()->default(NULL);
		$table->integer('user_id')->nullable()->default(NULL);
		$table->integer('type')->nullable()->default(NULL);
		$table->integer('request_id')->nullable()->default(NULL);

        $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
		$table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('change_store_rooms');
    }
}
