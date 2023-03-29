<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateResumesTable extends Migration
{
    public function up()
    {
        Schema::create('resumes', function (Blueprint $table) {

		$table->integer('id',10)->unsigned();
		$table->string('name');
		$table->string('email')->nullable()->default(NULL);
		$table->string('phone');
		$table->text('text')->nullable()->default(NULL);
		$table->text('description')->nullable()->default(NULL);
		$table->text('resume')->nullable()->default(NULL);
		$table->integer('recruitment_notice_id');
        $table->foreign('recruitment_notice_id')->references('id')->on('recruitment_notices')->onDelete('cascade');
		$table->integer('status')->default('0');
		$table->integer('card_id')->default('6');
        $table->foreign('card_id')->references('id')->on('cards')->onDelete('cascade');

        $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
		$table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('resumes');
    }
}
