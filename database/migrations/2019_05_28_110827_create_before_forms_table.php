<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBeforeFormsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('before_forms', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('service_id')->unsigned();
            $table->foreign('service_id')->references('id')->on('services');
            $table->string('type');
            $table->string('placeholder')->nullable();
            $table->string('name');
            $table->integer('required')->default(false);
            $table->string('value')->nullable();
            $table->string('class')->nullable();
            $table->string('help')->nullable();
            $table->text('file')->nullable();
            $table->string('data_save_level' , 110)->nullable();
            $table->string('data_display_level' , 110)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('before_forms');
    }
}
