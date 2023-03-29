<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateCallcentersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('callcenters', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('sms_id')->unsigned()->nullable();
            $table->foreign('sms_id')->references('id')->on('sms');
            $table->integer('reg_id')->unsigned()->nullable();
            $table->foreign('reg_id')->references('id')->on('users');
            $table->integer('user_id')->unsigned()->nullable();
            $table->foreign('user_id')->references('id')->on('users');
            $table->integer('agent_id')->unsigned()->nullable();
            $table->foreign('agent_id')->references('id')->on('users');
            $table->integer('representation_id')->unsigned()->nullable();
            $table->foreign('representation_id')->references('id')->on('representations');
            $table->integer('type');
            $table->string('request_type')->nullable();
            $table->integer('status');
            $table->text('description')->nullable();
            $table->string('date_expire')->nullable();
            $table->string('time_expire')->nullable();
            $table->string('fast_expire')->nullable();
            $table->integer('reason_id')->nullable();

            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
		    $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('callcenters');
    }
}
