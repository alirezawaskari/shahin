<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('services', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
            $table->string('app_title');
            $table->string('factor_title');
            $table->string('en_title');
            $table->string('slug');
            $table->text('demo');
            $table->text('content')->nullable();
            $table->text('left_content')->nullable();
            $table->text('icon');
            $table->text('thumb');
            $table->text('description')->nullable();
            $table->integer('status');
            $table->integer('isShow')->default(false);
            $table->integer('isCard')->default(false);
            $table->integer('special')->default(false);
            $table->integer('multiple')->default(true);
            $table->integer('items')->default(false);
            $table->integer('sms')->default(false);
            $table->string('sms_text')->default(false);
            $table->string('sms_replace')->default(false);
            $table->unsignedBigInteger('price');
            $table->unsignedBigInteger('total_price');
            $table->unsignedBigInteger('tax');
            $table->integer('profit')->nullable()->default(NULL);
            $table->integer('wage_percent')->default(4);
		    $table->text('required')->nullable()->default(NULL);
            $table->text('gift')->nullable()->default(NULL);
		    $table->integer('center')->default(0);
		    $table->string('relation_slug')->nullable()->default(NULL);
		    $table->text('after_fields')->nullable()->default(NULL);
            $table->text('image')->nullable()->default(NULL);
            $table->integer('category_id')->nullable()->default(NULL);
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
            $table->integer('session')->default(0);
            $table->integer('pay_type')->default(0);
            $table->integer('purchase_type')->default(0);
            $table->text('image_app')->nullable()->default(NULL);
            $table->text('img_box_1')->nullable()->default(NULL);
            $table->text('img_box_3')->nullable()->default(NULL);
            $table->text('txt_box_1')->nullable()->default(NULL);
            $table->text('rules')->nullable()->default(NULL);
            $table->text('session_attributes')->nullable()->default(NULL);
            $table->integer('confirm')->default(0);
            $table->integer('star')->nullable()->default(NULL);
            $table->integer('challenge')->default(0);
            $table->integer('isContract')->default(1);
            $table->integer('is_change_factor_price')->default(0);
            $table->integer('min_credit')->nullable()->default(NULL);
            $table->integer('max_credit')->nullable()->default(NULL);
            $table->integer('ability_invite_friend')->default(0);

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
        Schema::dropIfExists('services');
    }
}
