<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateVipUsersTable extends Migration
{
    public function up()
    {
        Schema::create('vip_users', function (Blueprint $table) {

		$table->integer('id');
		$table->integer('user_id');
        $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
		$table->string('type',110)->default('VIP');//VIP || SOS

        $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
		$table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('vip_users');
    }
}
