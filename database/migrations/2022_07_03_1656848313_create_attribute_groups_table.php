<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateAttributeGroupsTable extends Migration
{
    public function up()
    {
        Schema::create('attribute_groups', function (Blueprint $table) {

		$table->integer('id');
		$table->string('title');
		$table->integer('category_id');
        $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');

		$table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
		$table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('attribute_groups');
    }
}
