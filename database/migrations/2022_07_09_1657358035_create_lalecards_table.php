<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateLalecardsTable extends Migration
{
    public function up()
    {
        Schema::create('lalecards', function (Blueprint $table) {

		$table->integer('id');
		$table->string('name',110);
		$table->string('family',110);
		$table->string('city',110);
		$table->timestamp('post_date')->nullable()->default(NULL);

		$table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
		$table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('lalecards');
    }
}
