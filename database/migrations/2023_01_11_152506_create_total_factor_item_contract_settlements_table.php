<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTotalFactorItemContractSettlementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('total_factor_item_contract_settlements', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('item_id')->unsigned();
            $table->foreign('item_id')->references('id')->on('total_factor_items');
            $table->integer('applicant_id')->unsigned();
            $table->foreign('applicant_id')->references('id')->on('users');
            $table->integer('mali_id')->nullable()->unsigned();
            $table->foreign('mali_id')->references('id')->on('users');
            $table->integer('superadmin_id')->nullable()->unsigned();
            $table->foreign('superadmin_id')->references('id')->on('users');
            $table->integer('deliverer_id')->nullable()->unsigned();
            $table->foreign('deliverer_id')->references('id')->on('users');
            $table->text('img_request_contract_settlement')->nullable();
            $table->text('description_request_contract_settlement');
            $table->text('img_warranty_delivery_receipt')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('total_factor_item_contract_settlement');
    }
}
