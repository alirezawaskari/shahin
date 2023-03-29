<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateWorkCalendarsTable extends Migration
{
    public function up()
    {
        Schema::create('work_calendars', function (Blueprint $table) {

		$table->integer('id');
		$table->integer('center_id')->nullable()->default(NULL);
        $table->foreign('center_id')->references('id')->on('centers')->onDelete('cascade');
		$table->integer('user_id')->nullable()->default(NULL);
        $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
		$table->string('date',191)->nullable()->default(NULL);
		$table->string('date_en',191)->nullable()->default(NULL);
		$table->string('time_intervals',191)->nullable()->default(NULL);
		$table->string('start_time',191)->nullable()->default(NULL);
		$table->string('end_time',191)->nullable()->default(NULL);

		$table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
		$table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('work_calendars');
    }
}
