<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAgencyTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('agency_types', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
            $table->string('en_title');
            $table->string('slug');
            $table->text('image');
            $table->unsignedBigInteger('total_price');
            $table->unsignedBigInteger('prepayment');
            $table->text('content');
            $table->integer('is_show')->default(true);

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
        Schema::dropIfExists('agency_types');
    }
}
