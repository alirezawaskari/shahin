<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateTotalFactorItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('total_factor_items', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('factor_id')->nullable()->unsigned();
            $table->foreign('factor_id')->references('id')->on('factors');
            $table->integer('total_factor_item_status_id')->nullable()->unsigned();
            $table->foreign('total_factor_item_status_id')->references('id')->on('total_factor_item_status');
            $table->integer('supporter_id')->nullable()->unsigned();
            $table->foreign('supporter_id')->references('id')->on('users');
            $table->integer('status_mali')->default(false);
            $table->integer('confirm')->default(false);
            $table->text('contract_values')->nullable();
            $table->text('factor_values')->nullable();
            $table->string('guarantee_type');
            $table->string('file_number_mali');
            $table->unsignedBigInteger('total_price');
            $table->unsignedBigInteger('credit');
            $table->integer('installments_num')->default(false);
            $table->integer('percent_fines')->default(2);
            $table->integer('remember')->default(0);
            $table->timestamp('approval_date')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('expired_at')->nullable(true);

            $table->softDeletes();
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
        Schema::dropIfExists('total_factor_items');
    }
}
