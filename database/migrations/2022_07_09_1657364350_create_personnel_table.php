<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreatePersonnelTable extends Migration
{
    //join the employees table later
    public function up()
    {
        Schema::create('personnel', function (Blueprint $table) {

		$table->integer('id');
		$table->integer('parent_id')->nullable()->default(NULL);
        $table->foreign('parent_id')->references('id')->on('personnel')->onDelete('cascade');
		$table->integer('user_id')->nullable()->default(NULL);
        $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
		$table->string('working_side',191)->nullable()->default(NULL);

        $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
		$table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('personnel');
    }
}
