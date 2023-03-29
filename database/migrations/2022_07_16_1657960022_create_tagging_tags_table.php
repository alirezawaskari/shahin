<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateTaggingTagsTable extends Migration
{
    public function up()
    {
        Schema::create('tagging_tags', function (Blueprint $table) {

		$table->integer('id')->unsigned();
		$table->integer('tag_group_id')->unsigned()->nullable()->default(NULL);
        $table->foreign('tag_group_id')->references('id')->on('tagging_tag_group_id')->onDelete('cascade');
		$table->string('slug',125);
		$table->string('name',125);
		$table->tinyInteger('suggest')->default('0');
		$table->integer('count')->unsigned()->default('0');

        $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('tagging_tags');
    }
}
