<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateCenterRequestsTable extends Migration
{
    public function up()
    {
        Schema::create('center_requests', function (Blueprint $table) {

		$table->integer('id')->unsigned();
		$table->integer('center_category_id');
        $table->foreign('center_category_id')->references('id')->on('center_categories')->onDelete('cascade');
		$table->integer('user_id')->nullable()->default(NULL);
        $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
		$table->string('subject',110)->nullable()->default(NULL);
		$table->string('name',110)->nullable()->default(NULL);
		$table->string('phone',100)->default('02634612248');
		$table->string('connections',110)->nullable()->default(NULL);
		$table->string('type',110)->default('service_provider');
		$table->text('image')->nullable()->default(NULL);
		$table->integer('status')->default('0');
		$table->integer('city_id')->unsigned()->nullable()->default(NULL);
        $table->foreign('city_id')->references('id')->on('cities')->onDelete('cascade');
		$table->integer('province_id')->nullable()->default(NULL);
		$table->foreign('province_id')->references('id')->on('provinces')->onDelete('cascade');
		$table->integer('representation_id')->nullable()->default(NULL);
        $table->foreign('representation_id')->references('id')->on('representations')->onDelete('cascade');
        $table->text('description')->nullable()->default(NULL);
		$table->text('answer')->nullable()->default(NULL);

		$table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
		$table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('center_requests');
    }
}
