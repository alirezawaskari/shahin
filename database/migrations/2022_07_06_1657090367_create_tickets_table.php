<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateTicketsTable extends Migration
{
    public function up()
    {
        Schema::create('tickets', function (Blueprint $table) {

		$table->integer('id');
		$table->integer('user_id');//ticket sender
        $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        $table->morphs('ticketable', 'ticketable_index');//representation Models || ghorfeOnlineList Models
		$table->text('file')->nullable()->default(NULL);
		$table->string('title',200);
		$table->text('content');
		$table->integer('status')->default('0');

		$table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
		$table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        $table->softDeletes();
        });

        //ticket receivers
        Schema::create('ticket_user', function (Blueprint $table) {
            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->integer('ticket_id')->unsigned();
            $table->foreign('ticket_id')->references('id')->on('tickets')->onDelete('cascade');

            $table->primary(['user_id' , 'ticket_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('tickets');
    }
}
