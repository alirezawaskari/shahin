<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateUserPerformancesTable extends Migration
{
    public function up()
    {
        Schema::create('user_performances', function (Blueprint $table) {
		$table->integer('id');
		$table->integer('factor_item_id');
        $table->foreign('factor_item_id')->references('id')->on('factor_items')->onDelete('cascade');
		$table->integer('callcenter_id')->nullable()->default(NULL);
        $table->foreign('callcenter_id')->references('id')->on('users')->onDelete('cascade');
		$table->integer('user_id')->nullable()->default(NULL);
        $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
		$table->bigInteger('price')->nullable()->default(NULL);
		$table->bigInteger('ancillary_costs')->nullable()->default(NULL);
		$table->bigInteger('tax')->default('0');
		$table->text('description')->nullable()->default(NULL);
        
		$table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
		$table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_performances');
    }
}
