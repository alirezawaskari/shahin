<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePurchaseInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchase_invoices', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('supplier_id')->nullable()->unsigned();
            $table->foreign('supplier_id')->references('id')->on('suppliers');
            $table->integer('user_id')->nullable()->unsigned();
            $table->foreign('user_id')->references('id')->on('users');
            $table->integer('representation_id')->nullable()->unsigned();
            $table->foreign('representation_id')->references('id')->on('representations');
            $table->integer('type')->default(false);
            $table->unsignedBigInteger('total_price')->nullable(true);
            $table->unsignedBigInteger('discount')->nullable(true);
            $table->unsignedBigInteger('taxation')->nullable(true);
            $table->integer('installments_num')->nullable(true)->default(false);
            $table->unsignedBigInteger('extra_pay')->nullable(true);
            $table->text('factor_image')->nullable(true);
            $table->text('values')->nullable(true);
            $table->text('list');
            $table->integer('status')->default(false);
            $table->unsignedBigInteger('postage')->nullable(true);
            $table->text('post_image')->nullable(true);
            $table->integer('pay_postage')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('purchase_invoices');
    }
}
