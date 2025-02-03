<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateCardsTable extends Migration
{
    public function up()
    {
        Schema::create('cards', function (Blueprint $table) {

		$table->integer('id');
        $table->morphs('cardable', 'cardable_index');//representation Models || ghorfeOnlineList Models
		$table->string('title',191)->nullable()->default(NULL);
		$table->string('type',110);
        $table->integer('is_show')->default(0);
        $table->string('description')->nullable()->default(NULL);

        $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
		$table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('cards');
    }
}
