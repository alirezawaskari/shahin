<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateAuthCodeTable extends Migration
{
    public function up()
    {
        Schema::create('auth_code', function (Blueprint $table) {

		$table->integer('id');
		$table->string('phone',110);
		$table->string('code',110);
		$table->string('expiration_time')->nullable()->default(NULL);
		$table->integer('time_code_again')->nullable()->default(NULL);
        
		$table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
		$table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        $table->softDeletes();

        });
    }

    public function down()
    {
        Schema::dropIfExists('auth_code');
    }
}
