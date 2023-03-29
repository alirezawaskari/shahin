<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateStoreRoomsTable extends Migration
{
    public function up()
    {
        Schema::create('store_rooms', function (Blueprint $table) {

		$table->integer('id');
        $table->integer('warehouse_keeper_id')->nullable()->default(NULL);
        $table->foreign('warehouse_keeper_id')->references('id')->on('users')->onDelete('cascade');
        $table->integer('parent_id')->nullable()->default(NULL);
        $table->foreign('parent_id')->references('id')->on('store_rooms')->onDelete('cascade');
		$table->string('name');
		$table->string('code');
		$table->string('address');
		$table->integer('representation_id')->nullable()->default(NULL);
        $table->foreign('representation_id')->references('id')->on('representations')->onDelete('cascade');
		$table->float('lat')->default('35.84');
		$table->float('lon')->default('51.8788');
		$table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
		$table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('store_rooms');
    }
}
