<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateAttributeItemsTable extends Migration
{
    public function up()
    {
        Schema::create('attribute_items', function (Blueprint $table) {

		$table->integer('id');
		$table->integer('attribute_id');
        $table->foreign('attribute_id')->references('id')->on('attributes')->onDelete('cascade');
		$table->string('title');
		$table->integer('status')->default('1');

		$table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
		$table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('attribute_items');
    }
}
