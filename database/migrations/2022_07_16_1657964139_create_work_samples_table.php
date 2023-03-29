<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateWorkSamplesTable extends Migration
{
    public function up()
    {
        Schema::create('work_samples', function (Blueprint $table) {

		$table->integer('id');
        $table->unsignedBigInteger('worksampleable_id');
        $table->string('worksampleable_type');//Service Models || Category Models
        $table->index(['worksampleable_id', 'worksampleable_type']);
		$table->string('title',199)->nullable()->default(NULL);
		$table->string('image',199)->nullable()->default(NULL);
		$table->integer('status')->default('1');

		$table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
		$table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('work_samples');
    }
}
