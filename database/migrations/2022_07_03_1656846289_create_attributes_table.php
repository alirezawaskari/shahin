<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateAttributesTable extends Migration
{
    public function up()
    {
        Schema::create('attributes', function (Blueprint $table) {

		$table->integer('id');
		$table->integer('attribute_group_id');
        $table->foreign('attribute_group_id')->references('id')->on('attribute_groups')->onDelete('cascade');
		$table->integer('category_id');
        $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
		$table->enum('type',['select','text','color','switch']);
		$table->string('title',110);
		$table->integer('isFilter')->default('0');
		$table->integer('is_show')->default('0');
		$table->integer('is_price')->default('0');

		$table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
		$table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('attributes');
    }
}
