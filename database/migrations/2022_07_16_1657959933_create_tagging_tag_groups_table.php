<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTaggingTagGroupsTable extends Migration
{
    public function up()
    {
        Schema::create('tagging_tag_groups', function (Blueprint $table) {

		$table->integer('id')->unsigned();
		$table->string('slug',125);
		$table->string('name',125);

        });
    }

    public function down()
    {
        Schema::dropIfExists('tagging_tag_groups');
    }
}
