<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRolesTable extends Migration
{
    public function up()
    {
        Schema::create('roles', function (Blueprint $table) {

		$table->integer('id',10)->unsigned();
		$table->integer('card_id',11)->nullable()->default('NULL');
        $table->foreign('card_id')->references('id')->on('cards')->onDelete('cascade');
		$table->string('name');
		$table->integer('tax',11)->default('0');
		$table->string('display_name')->nullable()->default('NULL');
		$table->string('description')->nullable()->default('NULL');
		$table->timestamp('created_at')->nullable()->default('NULL');
		$table->timestamp('updated_at')->nullable()->default('NULL');

        });

        Schema::create('role_user', function (Blueprint $table) {
            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->integer('role_id')->unsigned();
            $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');

            $table->primary(['user_id' , 'role_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('roles');
    }
}
