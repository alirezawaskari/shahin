<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateCentersTable extends Migration
{
    public function up()
    {
        Schema::create('centers', function (Blueprint $table) {

		$table->integer('id')->unsigned();
        $table->morphs('centerable', 'centerable_index');//Service Models || Category Models
		$table->integer('user_id')->nullable()->default(NULL);
        $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
		$table->integer('representation_id')->unsigned()->nullable()->default(NULL);
        $table->foreign('representation_id')->references('id')->on('representations')->onDelete('cascade');
		$table->integer('center_request_id')->nullable()->default(NULL);
        $table->foreign('center_request_id')->references('id')->on('center_requests')->onDelete('cascade');
        $table->string('title')->nullable()->default(NULL);
		$table->integer('pay_type')->default('2');
		$table->integer('purchase_type')->default('1');
		$table->float('lat')->default('35.84');
		$table->float('lon')->default('51.8788');
        $table->integer('city_id')->unsigned()->nullable()->default(NULL);
		$table->integer('province_id')->unsigned()->nullable()->default(NULL);
		$table->text('image')->nullable()->default(NULL);
		$table->integer('status');
        $table->text('contract_number')->default(NULL);
        $table->text('contract_file')->default(NULL);
		$table->integer('is_show')->default(0);
        $table->text('description')->nullable()->default(NULL);

        $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
		$table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('centers');
    }
}
