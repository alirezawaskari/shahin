<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreatePacksTable extends Migration
{
    public function up()
    {
        Schema::create('packs', function (Blueprint $table) {

		$table->integer('id');
		$table->string('title',191)->nullable()->default(NULL);
		$table->string('slug',191)->nullable()->default(NULL);
		$table->string('price',191)->nullable()->default(NULL);
		$table->integer('is_show')->nullable()->default(NULL);
		$table->text('review')->nullable()->default(NULL);
		$table->text('description')->nullable()->default(NULL);
		$table->string('image',191)->nullable()->default(NULL);

        $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
		$table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('packs');
    }
}
