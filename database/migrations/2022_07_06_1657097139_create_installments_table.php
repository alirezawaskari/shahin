<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateInstallmentsTable extends Migration
{
    public function up()
    {
        Schema::create('installments', function (Blueprint $table) {

		$table->integer('id');
		$table->integer('installmentable_id',10)->unsigned();
		$table->string('installmentable_type',110);//Service Models || Category Models
        $table->index(['installmentable_id', 'installmentable_type']);
		$table->integer('num');
		$table->integer('high_interval');
		$table->integer('down_interval');
		$table->bigInteger('high_installment_price')->nullable()->default(NULL);
		$table->bigInteger('down_installment_price')->nullable()->default(NULL);

        $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
		$table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('installments');
    }
}
