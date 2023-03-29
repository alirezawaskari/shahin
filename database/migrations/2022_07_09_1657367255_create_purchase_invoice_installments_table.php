<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreatePurchaseInvoiceInstallmentsTable extends Migration
{
    public function up()
    {
        Schema::create('purchase_invoice_installments', function (Blueprint $table) {

		$table->integer('id');
		$table->integer('purchase_invoice_id');
        $table->foreign('purchase_invoice_id')->references('id')->on('purchase_invoices')->onDelete('cascade');
        $table->integer('factor_id');
        $table->foreign('factor_id')->references('id')->on('factors')->onDelete('cascade');
		$table->bigInteger('price');
		$table->integer('status')->default('0');
        $table->timestamp('date')->nullable()->default(NULL);
		$table->timestamp('pay_date')->nullable()->default(NULL);

		$table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
		$table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('purchase_invoice_installments');
    }
}
