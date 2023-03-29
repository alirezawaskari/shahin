<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateProductStoreRoomTable extends Migration
{
    public function up()
    {
        Schema::create('product_store_room', function (Blueprint $table) {

		$table->integer('id');
		$table->integer('user_id')->nullable()->default(NULL);
        $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
		$table->integer('product_id');
        $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
		$table->integer('store_room_id');
        $table->foreign('store_room_id')->references('id')->on('store_rooms')->onDelete('cascade');
        $table->integer('storage_store_room_id');
        $table->foreign('storage_store_room_id')->references('id')->on('storage_store_rooms')->onDelete('cascade');
		$table->integer('category_store_room_id');
        $table->foreign('category_store_room_id')->references('id')->on('category_store_rooms')->onDelete('cascade');
		$table->integer('purchase_invoice_id')->nullable()->default(NULL);
        $table->foreign('purchase_invoice_id')->references('id')->on('purchase_invoices')->onDelete('cascade');
        $table->integer('initial_stock');
        $table->integer('stock');
		$table->bigInteger('sales_price')->nullable()->default(NULL);
		$table->bigInteger('purchase_price',20);
		$table->integer('purchase_type')->default('0');
		$table->integer('pay_type')->default('0');
		$table->integer('storage_num')->nullable()->default(NULL);
		$table->integer('row_num')->nullable()->default(NULL);
		$table->integer('col_num')->nullable()->default(NULL);
		$table->text('barcode')->nullable()->default(NULL);
		$table->bigInteger('discount')->nullable()->default(NULL);
		$table->integer('order_point')->nullable()->default(NULL);
		$table->integer('order_day')->nullable()->default(NULL);
		$table->timestamp('expire_date')->nullable()->default(NULL);
		$table->integer('expire_alert')->nullable()->default(NULL);
		$table->integer('storage')->nullable()->default(NULL);
		$table->integer('main_unit_id')->nullable()->default(NULL);
        $table->foreign('main_unit_id')->references('id')->on('counting_units')->onDelete('cascade');
		$table->integer('guarantee')->default('1');
		$table->integer('main_unit_value')->nullable()->default(NULL);
		$table->integer('sub_unit_id')->nullable()->default(NULL);
        $table->foreign('sub_unit_id')->references('id')->on('counting_units')->onDelete('cascade');
		$table->integer('sub_unit_value')->nullable()->default(NULL);
		$table->integer('measurement_unit_id')->nullable()->default(NULL);
        $table->foreign('measurement_unit_id')->references('id')->on('counting_units')->onDelete('cascade');

        $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
		$table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('product_store_room');
    }
}
