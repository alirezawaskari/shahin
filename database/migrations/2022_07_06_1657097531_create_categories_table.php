<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateCategoriesTable extends Migration
{
    public function up()
    {
        Schema::create('categories', function (Blueprint $table) {

		$table->bigInteger('id')->unsigned();
		$table->string('title');
		$table->string('en_title');
		$table->string('path')->nullable()->default(NULL);
		$table->integer('percent')->default('0');
		$table->integer('min_credit')->nullable()->default(NULL);
		$table->integer('max_credit')->nullable()->default(NULL);
		$table->string('slug');
		$table->text('image')->nullable()->default(NULL);
		$table->string('icon')->nullable()->default(NULL);
		$table->bigInteger('price')->default('0');
		$table->integer('tax')->default('0');
		$table->integer('displayOrder')->default('0');
		$table->integer('parent_id');
		$table->integer('depth')->nullable()->default(NULL);
		$table->tinyInteger('isActive')->default('0');
        $table->integer('is_change_factor_price')->default(0);

        $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
		$table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        $table->softDeletes();
        });

        Schema::create('related_category', function (Blueprint $table) {
            $table->integer('related_id')->unsigned();
            $table->foreign('related_id')->references('id')->on('categories')->onDelete('cascade');

            $table->integer('category_id')->unsigned();
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');

            $table->primary(['related_id' , 'category_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('categories');
    }
}
