<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFactorItemAttributesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('factor_item_attributes', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedBigInteger('factor_item_attributable_id')->nullable();
            $table->string('factor_item_attributable_type',100)->nullable();

            $table->double('prepayment_price')->default(0);
            $table->double('prepayment_debt')->default(0);
            $table->integer('prepayment_count')->default(0);
            $table->integer('prepayment_num')->default(0);
            $table->double('price_difference_installment')->default(0);
            $table->double('price_difference_total')->default(0);
            $table->double('price_difference_debt')->default(0);
            $table->double('registeration_debt')->default(0);

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
        Schema::dropIfExists('factor_item_attributes');
    }
}
