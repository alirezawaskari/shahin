<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateCenterServiceTariffTable extends Migration
{
    public function up()
    {
        Schema::create('center_service_tariff', function (Blueprint $table) {

		$table->integer('id');
		$table->integer('center_id');
        $table->foreign('center_id')->references('id')->on('centers')->onDelete('cascade');
		$table->integer('service_tariff_id');
        $table->foreign('service_tariff_id')->references('id')->on('service_tariffs')->onDelete('cascade');

        $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
		$table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('center_service_tariff');
    }
}
