<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLogMessages extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('log_messages', function (Blueprint $table) {
            $table->increments('id');
            $table->string('level')->nullable();
            $table->string('level_name')->nullable();
            $table->string('message')->nullable();
            $table->text('context')->nullable();
            $table->text('extra')->nullable();
            $table->string('logged_at')->nullable();
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
        Schema::dropIfExists('log_message');
    }
}
