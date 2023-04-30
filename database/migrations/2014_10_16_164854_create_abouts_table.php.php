<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAboutsTable1 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('abouts', function (Blueprint $table) {
            $table->increments('id');
            $table->text('content');
            $table->text('content2');
            $table->text('content_one');
            $table->text('content_two');
            $table->text('image_tow');
            $table->text('content_three');
            $table->text('image_three');
            $table->text('content_four');
            $table->text('image_four');
            $table->text('content_five');
            $table->text('image_five');
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
        Schema::dropIfExists('abouts');
    }
}
