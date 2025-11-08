<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateFactorItemServiceTariffTable extends Migration
{
    public function up()
    {
        Schema::create('factor_item_service_tariff', function (Blueprint $table) {

		$table->integer('id');
		$table->integer('service_tariff_id')->unsigned();
        $table->foreign('service_tariff_id')->references('id')->on('service_tariffs')->onDelete('cascade');
		$table->integer('factor_item_id')->unsigned();
        $table->foreign('factor_item_id')->references('id')->on('factor_items')->onDelete('cascade');
		$table->text('value')->nullable()->default(NULL);
        $table->text('description')->nullable()->default(NULL);
		$table->string('grouping',110)->nullable()->default(NULL);
		$table->bigInteger('discount')->nullable()->default(NULL);
		$table->integer('user_id')->nullable()->default(NULL);
		$table->bigInteger('price')->nullable()->default(NULL);
		$table->integer('treatment_duration')->default('1');
		$table->integer('session_number')->default('1');
		$table->integer('status')->default('0');

		$table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
		$table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('factor_item_service_tariff');
    }
}
