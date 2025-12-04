<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateServiceTariffsTable extends Migration
{
    public function up()
    {
        Schema::create('service_tariffs', function (Blueprint $table) {

		$table->integer('id');
		$table->string('name');
		$table->string('slug',110)->nullable()->default(NULL);
		$table->integer('price')->nullable()->default(NULL);//selling price to lale users
		$table->integer('purchase_price')->nullable()->default(NULL);//purchase price from the center
		$table->integer('consumer_price')->nullable()->default(NULL);//consumer price
        $table->integer('multi_stage')->default(1);//1 or 2
        $table->integer('tariff_id')->nullable()->default(NULL);
        $table->foreign('tariff_id')->references('id')->on('tariffs')->onDelete('cascade');
        $table->integer('service_id')->nullable()->default(NULL);
        $table->foreign('service_id')->references('id')->on('services')->onDelete('cascade');
		$table->integer('service_index_tariff_id')->nullable()->default(NULL);
        $table->foreign('service_index_tariff_id')->references('id')->on('service_index_tariffs')->onDelete('cascade');
        $table->integer('status')->default('0');
		$table->integer('purchase_type')->nullable()->default(NULL);
		$table->integer('pay_type')->nullable()->default(NULL);
		$table->integer('session_number')->default('1');
		$table->integer('treatment_duration')->default('1');
		$table->integer('center_id1')->unsigned()->nullable()->default(NULL); //extra field

        $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
		$table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('service_tariffs');
    }
}
