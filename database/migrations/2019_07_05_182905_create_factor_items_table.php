<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateFactorItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('factor_items', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('factor_id')->nullable()->unsigned();
            $table->foreign('factor_id')->references('id')->on('factors');
            $table->integer('total_factor_item_id')->nullable()->default(NULL);
            $table->foreign('total_factor_item_id')->references('id')->on('total_factor_items');
            $table->integer('supporter_id')->nullable()->default(NULL);
            $table->foreign('supporter_id')->references('id')->on('users');
            $table->integer('supervisor_id')->nullable()->default(NULL);
            $table->foreign('supervisor_id')->references('id')->on('factor_items');
            $table->morphs('factoritemable', 'factoritemable_index');//Service Models || Category Models
            $table->integer('service_item_id')->nullable()->unsigned();
            $table->foreign('service_item_id')->references('id')->on('service_items');
            $table->integer('financial_plans_type_id')->nullable()->unsigned();
            $table->foreign('financial_plans_type_id')->references('id')->on('financial_plans_types');
            $table->integer('center_id')->nullable()->unsigned();
            $table->foreign('center_id')->references('id')->on('centers');
            $table->integer('count');
            $table->unsignedBigInteger('price');
            $table->integer('complete')->default(false);
            $table->text('values')->nullable();
            $table->mediumText('date')->nullable();
            $table->integer('status_factor')->default(0);
            $table->integer('status_mali')->default(0);
            $table->integer('percent_fines')->default(2);
            $table->integer('status')->default(0);
            $table->string('guarantee_type',110)->default('check');
            $table->text('factor_image')->nullable()->default(NULL);
            $table->string('file_number')->nullable()->default(NULL);
            $table->integer('type')->default(0);
            $table->bigInteger('credit')->nullable()->default(NULL);
            $table->text('resnumber')->nullable()->default(NULL);
            $table->integer('remember')->default(0);
            $table->integer('installments_num')->nullable()->default(NULL);
            $table->bigInteger('installments_price')->nullable()->default(NULL);
            $table->bigInteger('discount')->nullable()->default(NULL);
            $table->text('factor_values')->nullable()->default(NULL);
            $table->text('confirms')->nullable()->default(NULL);
            $table->integer('confirm')->default(0);
            $table->integer('verify_mali')->default(0);
            $table->integer('verify_manager')->default(0);
            $table->bigInteger('total_price')->nullable()->default(NULL);
            $table->integer('purchase_type')->default(0);

            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
		    $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('factor_items');
    }
}
