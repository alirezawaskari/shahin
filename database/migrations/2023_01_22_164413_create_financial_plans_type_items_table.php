<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFinancialPlansTypeItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('financial_plans_type_items', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('financial_plans_type_id')->nullable()->default(NULL);
            $table->foreign('financial_plans_type_id')->references('id')->on('financial_plans_types');
            $table->morphs('financialplanstypeitemable', 'financialplanstypeitemable_index');//Service Models || Category Models

            $table->softDeletes();
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
        Schema::dropIfExists('financial_plans_type_items');
    }
}
