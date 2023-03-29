<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateSuppliersTable extends Migration
{
    public function up()
    {
        Schema::create('suppliers', function (Blueprint $table) {

		$table->integer('id');
		$table->integer('user_id')->nullable()->default(NULL);
        $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
		$table->string('name',110);
		$table->string('manager',110);
		$table->string('address');
		$table->string('phone',110)->nullable()->default(NULL);

		$table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
		$table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('suppliers');
    }
}
