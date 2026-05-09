<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateExecutionSessionsTable extends Migration
{
    public function up()
    {
        Schema::create('execution_sessions', function (Blueprint $table) {

		$table->integer('id');
		$table->integer('factor_item_service_tariff_id')->nullable()->default(NULL);
        $table->foreign('factor_item_service_tariff_id')->references('id')->on('factor_item_service_tariff')->onDelete('cascade');
		$table->integer('factor_item_id');
        $table->foreign('factor_item_id')->references('id')->on('factor_items')->onDelete('cascade');
		$table->integer('user_id')->nullable()->default(NULL);
        $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
		$table->integer('agent_id')->nullable()->default(NULL);
        $table->foreign('agent_id')->references('id')->on('users')->onDelete('cascade');
		$table->integer('confirmer_id')->nullable()->default(NULL);
        $table->foreign('confirmer_id')->references('id')->on('users')->onDelete('cascade');
		$table->integer('assistant_id')->nullable()->default(NULL);
        $table->foreign('assistant_id')->references('id')->on('users')->onDelete('cascade');
		$table->integer('user_performance_id')->nullable()->default(NULL);
        $table->foreign('user_performance_id')->references('id')->on('user_performances')->onDelete('cascade');
		$table->bigInteger('laboratory_price')->default(0);
		$table->timestamp('date')->nullable()->default(DB::raw('CURRENT_TIMESTAMP'));
		$table->text('description_before')->nullable()->default(NULL);
		$table->text('description')->nullable()->default(NULL);
		$table->integer('status')->default(0);

        $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
		$table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('execution_sessions');
    }
}
