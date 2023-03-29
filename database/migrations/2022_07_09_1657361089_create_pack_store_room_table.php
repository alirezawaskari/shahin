<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreatePackStoreRoomTable extends Migration
{
    public function up()
    {
        Schema::create('pack_store_room', function (Blueprint $table) {

		$table->integer('id');
		$table->integer('pack_id')->nullable()->default(NULL);
        $table->foreign('pack_id')->references('id')->on('packs')->onDelete('cascade');
		$table->integer('store_room_id')->nullable()->default(NULL);
        $table->foreign('store_room_id')->references('id')->on('store_rooms')->onDelete('cascade');
		$table->integer('category_store_room_id')->nullable()->default(NULL);
        $table->foreign('category_store_room_id')->references('id')->on('category_store_rooms')->onDelete('cascade');
		$table->integer('purchase_type')->nullable()->default(NULL);
		$table->integer('pay_type')->nullable()->default(NULL);
		$table->integer('stock')->nullable()->default(NULL);
		$table->bigInteger('sales_price')->nullable()->default(NULL);
		$table->bigInteger('purchase_price')->nullable()->default(NULL);
		$table->integer('address')->nullable()->default(NULL);
		$table->integer('barcode')->nullable()->default(NULL);
		$table->integer('main_unit_id')->nullable()->default(NULL);
		$table->integer('sub_unit_id')->nullable()->default(NULL);
		$table->integer('main_unit_value')->nullable()->default(NULL);
		$table->integer('sub_unit_value')->nullable()->default(NULL);

        $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
		$table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pack_store_room');
    }
}
