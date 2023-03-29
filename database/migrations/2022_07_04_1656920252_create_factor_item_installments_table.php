<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateFactorItemInstallmentsTable extends Migration
{
    public function up()
    {
        Schema::create('factor_item_installments', function (Blueprint $table) {

		$table->integer('id');
		$table->bigInteger('price')->unsigned();
		$table->integer('level');
		$table->integer('status')->default(0);
		$table->text('resnumber')->nullable()->default(NULL);
		$table->integer('factor_item_id');
        $table->foreign('factor_item_id')->references('id')->on('factor_items')->onDelete('cascade');
		$table->integer('factor_id')->nullable()->default(NULL);
        $table->foreign('factor_id')->references('id')->on('factors')->onDelete('cascade');
		$table->text('date')->nullable()->default(NULL);
		$table->text('pay_date')->nullable()->default(NULL);
		$table->integer('remember')->default(0);

		$table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
		$table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('factor_item_installments');
    }
}
