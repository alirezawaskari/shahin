<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateRecruitmentNoticesTable extends Migration
{
    public function up()
    {
        Schema::create('recruitment_notices', function (Blueprint $table) {

		$table->integer('id');
		$table->string('title',100);
		$table->text('description');
		$table->integer('status')->default('1');
		$table->text('image')->nullable()->default(NULL);
		$table->integer('province_id')->unsigned();
        $table->foreign('province_id')->references('id')->on('provinces')->onDelete('cascade');

        $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
		$table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('recruitment_notices');
    }
}
