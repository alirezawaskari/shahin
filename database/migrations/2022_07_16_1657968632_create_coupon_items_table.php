<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateCouponItemsTable extends Migration
{
    public function up()
    {
        Schema::create('coupon_items', function (Blueprint $table) {

		$table->integer('id');
		$table->integer('coupon_id')->nullable()->default(NULL);
        $table->foreign('coupon_id')->references('id')->on('coupons')->onDelete('cascade');
        $table->morphs('couponitemable', 'coupon_itemable_index');//ProductAttribute Models || Category Models
		$table->integer('center_id')->nullable()->default(NULL);
        $table->foreign('center_id')->references('id')->on('centers')->onDelete('cascade');

		$table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
		$table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('coupon_items');
    }
}
