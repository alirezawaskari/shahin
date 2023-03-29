<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateGroupsmsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('groupsms', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
            $table->text('message');
            $table->integer('status')->default(false);
            $table->integer('flag');
            $table->string('send_at')->nullable();

            $table->string('type');
            $table->unsignedInteger('phone_list_id')->nullable();
            $table->foreign('phone_list_id')->references('id')->on('phone_lists')->onDelete('cascade');
            $table->text('phones')->nullable();

            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        });

        Schema::create('groupsms_service', function (Blueprint $table) {
            $table->integer('service_id')->unsigned();
            $table->foreign('service_id')->references('id')->on('services')->onDelete('cascade');

            $table->integer('groupsms_id')->unsigned();
            $table->foreign('groupsms_id')->references('id')->on('groupsms')->onDelete('cascade');

            $table->primary(['service_id' , 'groupsms_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('groupsms');
    }
}
