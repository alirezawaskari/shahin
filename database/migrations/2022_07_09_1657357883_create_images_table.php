<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateImagesTable extends Migration
{
    //unused in new shahin
    public function up()
    {
        Schema::create('images', function (Blueprint $table) {

		$table->integer('id');
		$table->text('value');
		$table->integer('service_id')->unsigned();
        $table->foreign('service_id')->references('id')->on('services')->onDelete('cascade');

		$table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
		$table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('images');
    }
}
