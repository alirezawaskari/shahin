<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateSlidersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sliders', function (Blueprint $table) {
            $table->increments('id');
            $table->text('img');
            $table->text('title')->nullable(true);
            $table->integer('type')->nullable()->default(NULL);
            $table->text('link')->nullable(true);
            $table->integer('sort')->unsigned()->nullable()->default(NULL);
            $table->integer('status')->default('1');
            $table->integer('category_id')->nullable()->default(NULL);
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');

            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
		    $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sliders');
    }
}
