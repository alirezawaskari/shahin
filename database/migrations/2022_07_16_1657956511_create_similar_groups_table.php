<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSimilarGroupsTable extends Migration
{
    public function up()
    {
        Schema::create('similar_groups', function (Blueprint $table) {

		$table->integer('id');
		$table->integer('similarable_id')->nullable()->default(NULL);
		$table->string('similar_model',199)->nullable()->default(NULL);//Category Models || Service Models || CategoryArticle Models
		$table->integer('relation_id')->nullable()->default(NULL);
		$table->string('relation_model',100)->default(NULL);//Category Models || Service Models || CategoryArticle Models

        });
    }

    public function down()
    {
        Schema::dropIfExists('similar_groups');
    }
}
