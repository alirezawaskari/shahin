<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateInviteCategoriesTable extends Migration
{
    public function up()
    {
        Schema::create('invite_categories', function (Blueprint $table) {

		$table->integer('id');
        $table->morphs('invitecategoryable', 'invite_categoryable_index');//Product Models || Serivce Models
		$table->string('percent',191)->nullable()->default(NULL);

		$table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
		$table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('invite_categories');
    }
}
