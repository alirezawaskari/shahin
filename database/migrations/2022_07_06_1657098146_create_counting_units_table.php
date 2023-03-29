<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateCountingUnitsTable extends Migration
{
    public function up()
    {
        Schema::create('counting_units', function (Blueprint $table) {

		$table->bigInteger('id',20)->unsigned();
		$table->string('title');
		$table->string('type')->default('packing');

		$table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
		$table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('counting_units');
    }
}
