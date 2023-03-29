<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateCommentsTable extends Migration
{
    public function up()
    {
        Schema::create('comments', function (Blueprint $table) {

		$table->integer('id');
		$table->integer('user_id')->nullable()->default(NULL);
        $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
		$table->text('message')->nullable()->default(NULL);
        $table->unsignedBigInteger('commentable_id');
        $table->string('commentable_type');//Service Models || Product Models
        $table->index(['commentable_id', 'commentable_type']);
		$table->integer('score')->nullable()->default(NULL);
		$table->integer('active')->nullable()->default(NULL);

		$table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
		$table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('comments');
    }
}
