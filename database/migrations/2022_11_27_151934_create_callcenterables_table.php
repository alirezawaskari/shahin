<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCallcenterablesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('callcenterables', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('callcenter_id');
            $table->foreign('callcenter_id')->references('id')->on('callcenters')->onDelete('cascade');
            $table->unsignedBigInteger('callcenterable_id');
            $table->string('callcenterable_type');
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
        Schema::dropIfExists('callcenterables');
    }
}
