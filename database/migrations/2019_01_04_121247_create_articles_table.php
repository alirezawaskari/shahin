<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateArticlesTable extends Migration
{
    public function up()
    {
        Schema::create('articles', function (Blueprint $table) {

		$table->integer('id')->unsigned();
		$table->integer('user_id')->unsigned();
        $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        $table->morphs('articleable', 'articleable_index');//representation Models || ghorfeOnlineList Models
		$table->integer('category_article_id')->default('1');
        $table->foreign('category_article_id')->references('id')->on('category_articles')->onDelete('cascade');
		$table->string('title');
        $table->string('slug_title');
		$table->string('slug');
		$table->text('old_image');
        $table->text('image_url');
		$table->text('thumb_url');
		$table->text('tag')->nullable()->default(NULL);
		$table->text('description')->nullable()->default(NULL);
		$table->string('summary',220);
		$table->integer('status');

        $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
		$table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('articles');
    }
}
