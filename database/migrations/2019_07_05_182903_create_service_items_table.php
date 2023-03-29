<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateServiceItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('service_items', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('service_id')->nullable()->unsigned();
            $table->foreign('service_id')->references('id')->on('services');
            $table->string('slug' , 110)->nullable();
            $table->string('title' , 255)->nullable();
            $table->unsignedBigInteger('price');
            $table->integer('status');
            $table->integer('isShow')->default(false);
            $table->text('image')->nullable()->default(NULL);
            $table->text('content')->nullable();

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
        Schema::dropIfExists('service_items');
    }
}
