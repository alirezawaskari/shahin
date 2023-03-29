<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateNewsTable extends Migration
{
    public function up()
    {
        Schema::create('news', function (Blueprint $table) {

		$table->integer('id');
		$table->integer('user_id')->nullable()->default(NULL);
        $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
		$table->string('title',191)->nullable()->default(NULL);
		$table->string('subject',191)->nullable()->default(NULL);
		$table->string('slug',191)->nullable()->default(NULL);
		$table->string('image',191)->nullable()->default(NULL);
		$table->text('description')->nullable()->default(NULL);
		$table->integer('status')->nullable()->default(NULL);

        $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
		$table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('news');
    }
}
