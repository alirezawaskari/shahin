<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTotalFactorItemInstallmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('total_factor_item_installments', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedBigInteger('price');
            $table->integer('level')->default(false);
            $table->integer('status')->default(false);
            $table->string('resnumber');
            $table->integer('total_factor_item_id')->nullable()->unsigned();
            $table->foreign('total_factor_item_id')->references('id')->on('total_factor_items');
            $table->integer('factor_id')->nullable()->unsigned();
            $table->foreign('factor_id')->references('id')->on('factors');
            $table->timestamp('date')->nullable(true);
            $table->timestamp('pay_date')->nullable(true);
            $table->integer('remember')->default(0);
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
        Schema::dropIfExists('total_factor_item_installments');
    }
}
