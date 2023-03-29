<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateUserRequestsTable extends Migration
{
    public function up()
    {
        Schema::create('user_requests', function (Blueprint $table) {

		$table->integer('id');
		$table->integer('user_id');
		$table->integer('card_id');
		$table->integer('status')->default('0');
		$table->text('description');

		$table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
		$table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_requests');
    }
}
