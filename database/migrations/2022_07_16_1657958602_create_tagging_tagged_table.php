<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTaggingTaggedTable extends Migration
{
    public function up()
    {
        Schema::create('tagging_tagged', function (Blueprint $table) {

		$table->integer('id')->unsigned();
        $table->unsignedBigInteger('taggable_id');
        $table->string('taggable_type');//Article Models || Pack Models || Product Models
        $table->index(['taggable_id', 'taggable_type']);
		$table->string('tag_name',125);
		$table->string('tag_slug',125);

        });
    }

    public function down()
    {
        Schema::dropIfExists('tagging_tagged');
    }
}
