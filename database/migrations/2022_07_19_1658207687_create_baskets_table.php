<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateBasketsTable extends Migration
{
    public function up()
    {
        Schema::create('baskets', function (Blueprint $table) {

		$table->integer('id');
		$table->integer('user_id');
        $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
		$table->integer('basketable_id');
        $table->string('type',191)->nullable()->default(NULL);
		$table->integer('store_room')->nullable()->default(NULL);
		$table->integer('root')->nullable()->default(NULL);
        $table->integer('center_id')->nullable()->default(NULL);
        $table->foreign('center_id')->references('id')->on('centers')->onDelete('cascade');
		$table->integer('attribute_item_id')->nullable()->default(NULL);
        $table->foreign('attribute_item_id')->references('id')->on('attribute_items')->onDelete('cascade');
		$table->integer('pay_type')->nullable()->default(NULL);
		$table->integer('purchase_type')->nullable()->default(NULL);
		$table->integer('count')->nullable()->default(NULL);
		$table->integer('city_id')->nullable()->default(NULL);
        $table->foreign('city_id')->references('id')->on('cities')->onDelete('cascade');

		$table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
		$table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('baskets');
    }
}
