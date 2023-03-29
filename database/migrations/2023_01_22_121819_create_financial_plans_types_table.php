<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateFinancialPlansTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('financial_plans_types', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('applicant_id')->unsigned();
            $table->foreign('applicant_id')->references('id')->on('users');
            $table->string('title' , 200);
            $table->bigInteger('price');
            $table->integer('prepayment_percent')->default(0);
            $table->integer('discount_percent')->nullable()->default(0);
            $table->text('guarantee_type')->nullable();
            $table->string('payment_type' , 110)->default('installment');
            $table->integer('number_pay_steps')->nullable();
            $table->integer('status')->default(0);
            $table->integer('is_show')->default(0);
            $table->integer('capacity')->default(1);
            $table->integer('reserved')->default(0);
            $table->timestamp('start_date')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('end_date')->default(DB::raw('CURRENT_TIMESTAMP'));

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
        Schema::dropIfExists('financial_plans_types');
    }
}
