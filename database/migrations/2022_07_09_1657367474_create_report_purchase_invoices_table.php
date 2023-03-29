<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateReportPurchaseInvoicesTable extends Migration
{
    public function up()
    {
        Schema::create('report_purchase_invoices', function (Blueprint $table) {

		$table->integer('id')->unsigned();
		$table->integer('user_id')->unsigned();
		$table->integer('purchase_invoice_id')->unsigned();
        $table->foreign('purchase_invoice_id')->references('id')->on('purchase_invoices')->onDelete('cascade');
		$table->text('report');

		$table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
		$table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('report_purchase_invoices');
    }
}
