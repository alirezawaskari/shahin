<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateAskedQuestionsTable extends Migration
{
    public function up()
    {
        Schema::create('asked_questions', function (Blueprint $table) {

		$table->integer('id');
        $table->unsignedBigInteger('askedquestionable_id');
        $table->string('askedquestionable_type');//Service Models
        $table->index(['askedquestionable_id', 'askedquestionable_type']);
		$table->text('question');
		$table->text('answer');

        $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
		$table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('asked_questions');
    }
}
