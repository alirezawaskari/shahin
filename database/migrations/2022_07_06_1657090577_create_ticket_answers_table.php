<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateTicketAnswersTable extends Migration
{
    public function up()
    {
        Schema::create('ticket_answers', function (Blueprint $table) {

		$table->integer('id');
		$table->integer('user_id');
        $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
		$table->integer('ticket_id');
        $table->foreign('ticket_id')->references('id')->on('tickets')->onDelete('cascade');
		$table->text('file')->nullable()->default(NULL);
		$table->text('content')->nullable()->default(NULL);

		$table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
		$table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('ticket_answers');
    }
}
