<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateReviewsTable extends Migration
{
    public function up()
    {
        Schema::create('reviews', function (Blueprint $table) {

		$table->integer('id');
		$table->integer('product_id')->nullable()->default(NULL);
        $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
		$table->integer('reviewable_id')->nullable()->default(NULL);
		$table->string('reviewable_type',191)->nullable()->default(NULL);//Center Models || StoreRoom Models
        $table->index(['reviewable_id', 'reviewable_type']);
		$table->integer('type')->nullable()->default(NULL);
		$table->text('text')->nullable()->default(NULL);

		$table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
		$table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('reviews');
    }
}
