<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateAgencyTypesTable extends Migration
{
    public function up()
    {
        Schema::create('agency_types', function (Blueprint $table) {

		$table->integer('id');
		$table->string('title');
        $table->integer('service_id');
        $table->foreign('service_id')->references('id')->on('services')->onDelete('cascade');
		$table->string('en_title');
		$table->string('slug');
		$table->text('image');
		$table->bigInteger('total_price');
		$table->bigInteger('prepayment');
		$table->text('content');
		$table->integer('is_show')->default(1);

        $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
		$table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('agency_types');
    }
}
