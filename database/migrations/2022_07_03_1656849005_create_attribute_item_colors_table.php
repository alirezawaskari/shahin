<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateAttributeItemColorsTable extends Migration
{
    public function up()
    {
        Schema::create('attribute_item_colors', function (Blueprint $table) {

		$table->integer('id');
		$table->integer('attribute_item_id');
        $table->foreign('attribute_item_id')->references('id')->on('attribute_items')->onDelete('cascade');
		$table->integer('color_id');
        $table->foreign('color_id')->references('id')->on('colors')->onDelete('cascade');

		$table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
		$table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        $table->softDeletes();

        });
    }

    public function down()
    {
        Schema::dropIfExists('attribute_item_colors');
    }
}
