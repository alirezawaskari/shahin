<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateArrangeContractsTable extends Migration
{
    public function up()
    {
        Schema::create('arrange_contracts', function (Blueprint $table) {

		$table->integer('id');
		$table->integer('owner_id')->nullable()->default(NULL);
        $table->foreign('owner_id')->references('id')->on('users')->onDelete('cascade');
		$table->integer('contract_id');
        $table->foreign('contract_id')->references('id')->on('contracts')->onDelete('cascade');
		$table->string('file',191)->nullable()->default(NULL);
		$table->integer('status')->default('0');
		$table->integer('user_id');
        $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
		$table->integer('no');

        $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
		$table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('arrange_contracts');
    }
}
