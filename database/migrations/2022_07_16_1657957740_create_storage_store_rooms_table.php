<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStorageStoreRoomsTable extends Migration
{
    public function up()
    {
        Schema::create('storage_store_rooms', function (Blueprint $table) {

		$table->integer('id');
		$table->string('title',191)->nullable()->default(NULL);

        });
    }

    public function down()
    {
        Schema::dropIfExists('storage_store_rooms');
    }
}
