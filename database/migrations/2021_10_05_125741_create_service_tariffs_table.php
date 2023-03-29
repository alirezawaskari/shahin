<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateServiceTariffsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('service_tariffs', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('slug');
            $table->unsignedBigInteger('price');
            $table->integer('tariff_id')->unsigned();
            $table->foreign('tariff_id')->references('id')->on('tariffs');
            $table->integer('status')->default(false);
            $table->integer('purchase_type')->default(false);
            $table->integer('pay_type')->default(false);
            $table->integer('session_number')->default(false);
            $table->integer('treatment_duration')->default(false);
            $table->integer('center_id')->nullable()->unsigned();
            $table->foreign('center_id')->references('id')->on('centers');
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
        Schema::dropIfExists('service_tariffs');
    }
}
