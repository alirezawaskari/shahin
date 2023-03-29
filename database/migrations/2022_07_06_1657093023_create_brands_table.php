<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateBrandsTable extends Migration
{
    public function up()
    {
        Schema::create('brands', function (Blueprint $table) {

		$table->integer('id');
		$table->text('title')->nullable()->default(NULL);
		$table->string('title_en',191)->nullable()->default(NULL);
		$table->string('description',200)->nullable()->default(NULL);
		$table->integer('type')->nullable()->default(NULL);
		$table->string('logo',191)->nullable()->default(NULL);
		$table->string('link',191)->nullable()->default(NULL);
		$table->string('registration_form',191)->nullable()->default(NULL);
		$table->integer('request_by')->nullable()->default(NULL);
		$table->integer('status')->default('1');

        $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
		$table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        $table->softDeletes();
        });

        Schema::create('brand_category', function (Blueprint $table) {
            $table->integer('category_id')->unsigned();
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');

            $table->integer('brand_id')->unsigned();
            $table->foreign('brand_id')->references('id')->on('brands')->onDelete('cascade');

            $table->primary(['category_id' , 'brand_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('brands');
    }
}
