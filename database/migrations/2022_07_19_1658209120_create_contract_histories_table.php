<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateContractHistoriesTable extends Migration
{
    public function up()
    {
        Schema::create('contract_histories', function (Blueprint $table) {

		$table->integer('id');
		$table->integer('contract_id')->nullable()->default(NULL);
        $table->foreign('contract_id')->references('id')->on('contracts')->onDelete('cascade');
		$table->integer('user_id')->nullable()->default(NULL);
        $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
		$table->string('file',191)->nullable()->default(NULL);

		$table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
		$table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('contract_histories');
    }
}
