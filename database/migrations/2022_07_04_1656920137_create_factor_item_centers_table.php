<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFactorItemCentersTable extends Migration
{
    public function up()
    {
        Schema::create('factor_item_centers', function (Blueprint $table) {

		$table->integer('id');
		$table->integer('factor_item_id');
        $table->foreign('factor_item_id')->references('id')->on('factor_items')->onDelete('cascade');
		$table->integer('center_id');
        $table->foreign('center_id')->references('id')->on('centers')->onDelete('cascade');
		$table->timestamp('deleted_at')->nullable()->default(NULL);
		$table->timestamp('created_at')->nullable()->default(NULL);
		$table->timestamp('updated_at')->nullable()->default(NULL);

        });
    }

    public function down()
    {
        Schema::dropIfExists('factor_item_centers');
    }
}
