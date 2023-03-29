<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChecksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('checks', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('checkable_id');
            $table->string('checkable_type');
            $table->tinyInteger('status');
            $table->double('amount');
            $table->unsignedInteger('creator_id');
            $table->foreign('creator_id')->references('id')->on('users');
            $table->string('exporter');
            $table->string('pay_to');
            $table->string('title');
            $table->string('serial_code');
            $table->timestamp('date');
            $table->integer('receipt_date')->nullable();
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
        Schema::dropIfExists('checks');
    }
}
