<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFactorItemInstallmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('factor_item_installments', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedBigInteger('price');
            $table->integer('level')->default(false);
            $table->integer('status')->default(false);
            $table->string('resnumber');
            $table->integer('factor_item_id')->nullable()->unsigned();
            $table->foreign('factor_item_id')->references('id')->on('factor_items');
            $table->integer('factor_id')->nullable()->unsigned();
            $table->foreign('factor_id')->references('id')->on('factors');
            $table->timestamp('date')->nullable(true);
            $table->timestamp('pay_date')->nullable(true);
            $table->string('remember');
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
        Schema::dropIfExists('factor_item_installments');
    }
}
