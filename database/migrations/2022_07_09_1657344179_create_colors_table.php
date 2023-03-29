<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateColorsTable extends Migration
{
    public function up()
    {
        Schema::create('colors', function (Blueprint $table) {

		$table->integer('id');
		$table->string('title',191)->nullable()->default(NULL);
		$table->string('color_palette',191)->nullable()->default(NULL);

        $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
		$table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('colors');
    }
}
