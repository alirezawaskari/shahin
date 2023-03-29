<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateBasketInstallmentsTable extends Migration
{
    public function up()
    {
        Schema::create('basket_installments', function (Blueprint $table) {

		$table->integer('id');
		$table->integer('basket_id')->nullable()->default(NULL);
        $table->foreign('basket_id')->references('id')->on('baskets')->onDelete('cascade');
		$table->bigInteger('prepayment',20)->nullable()->default(NULL);
		$table->bigInteger('installments_price')->nullable()->default(NULL);
		$table->bigInteger('total_price')->nullable()->default(NULL);
		$table->integer('installments_num')->nullable()->default(NULL);

        $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
		$table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('basket_installments');
    }
}
