<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateUserInvitesTable extends Migration
{
    public function up()
    {
        Schema::create('user_invites', function (Blueprint $table) {

		$table->integer('id');
		$table->integer('invite_category_id')->nullable()->default(NULL);
        $table->foreign('invite_category_id')->references('id')->on('categories')->onDelete('cascade');
		$table->string('mobile',191)->nullable()->default(NULL);
		$table->string('name',191)->nullable()->default(NULL);
		$table->string('family',191)->nullable()->default(NULL);
		$table->integer('user_id')->nullable()->default(NULL);
        $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
		$table->integer('status')->default('1');

		$table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
		$table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_invites');
    }
}
