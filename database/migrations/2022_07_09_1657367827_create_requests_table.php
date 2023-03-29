<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateRequestsTable extends Migration
{
    public function up()
    {
        Schema::create('requests', function (Blueprint $table) {

		$table->integer('id');
		$table->integer('user_id')->nullable()->default(NULL);
        $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
		$table->integer('card_id')->nullable()->default(NULL);
        $table->foreign('card_id')->references('id')->on('cards')->onDelete('cascade');
		$table->integer('store_room_id')->nullable()->default(NULL);
        $table->foreign('store_room_id')->references('id')->on('store_rooms')->onDelete('cascade');
		$table->integer('type')->nullable()->default(NULL);
		$table->integer('status')->default('1');
		$table->integer('confirmator')->nullable()->default(NULL);
        $table->foreign('confirmator')->references('id')->on('users')->onDelete('cascade');
		$table->integer('sender_id')->nullable()->default(NULL);
        $table->foreign('sender_id')->references('id')->on('users')->onDelete('cascade');
		$table->text('description')->nullable()->default(NULL);
		$table->string('file',191)->nullable()->default(NULL);

		$table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
		$table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('requests');
    }
}
