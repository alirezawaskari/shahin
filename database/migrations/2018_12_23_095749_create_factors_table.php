<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFactorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('factors', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->integer('callcenter_id')->nullable()->unsigned();
            $table->foreign('callcenter_id')->references('id')->on('users');
            $table->integer('coupon_id')->nullable()->unsigned();
            $table->foreign('coupon_id')->references('id')->on('coupons');
            $table->integer('factor_item_id')->nullable()->unsigned();
            $table->foreign('factor_item_id')->references('id')->on('factor_items');
            $table->integer('total_factor_item_id')->nullable()->unsigned();
            $table->foreign('total_factor_item_id')->references('id')->on('total_factor_items');
            $table->integer('representation_id')->nullable()->unsigned();
            $table->foreign('representation_id')->references('id')->on('representations');
            $table->integer('organization_id')->nullable()->unsigned();
            $table->foreign('organization_id')->references('id')->on('organizations');
            $table->integer('center_id')->nullable()->unsigned();
            $table->foreign('center_id')->references('id')->on('centers');
            $table->integer('support_id')->nullable()->unsigned();
            $table->foreign('support_id')->references('id')->on('users');
            $table->integer('task_id')->nullable()->unsigned();
            $table->foreign('task_id')->references('id')->on('callcenters');
            $table->text('resnumber')->nullable();
            $table->text('refnumber')->nullable();
            $table->unsignedBigInteger('price');
            $table->unsignedBigInteger('tax')->nullable();
            $table->unsignedBigInteger('count')->default('1');
            $table->string('type')->default('online');
            $table->integer('status')->default(false);
            $table->integer('confirm')->default(false);
            $table->integer('verify')->default(false);
            $table->integer('remember')->default(0);
            $table->string('number_card' , '128')->nullable()->default(NULL);
            $table->string('payment_code' , '128')->nullable()->default(NULL);
            $table->text('bill_image')->nullable()->default(NULL);
            $table->text('sheba_number')->nullable()->default(NULL);
            $table->integer('address_id')->nullable()->unsigned();
            $table->foreign('address_id')->references('id')->on('addresses');
            $table->string('title' , '110')->default('register');
            $table->integer('level')->default('1');
            $table->timestamp('expired_at')->default(DB::raw('CURRENT_TIMESTAMP'));

            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
		    $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->softDeletes();
            //unused field
            $table->integer('service_id')->nullable()->unsigned();
            $table->foreign('service_id')->references('id')->on('services');
            $table->integer('parent_id')->nullable()->unsigned();
            $table->foreign('parent_id')->references('id')->on('factors');
            $table->integer('factorable_id')->unsigned()->nullable(true);
            $table->string('factorable_type')->nullable(true);
            $table->string('request_date')->nullable(true);
            $table->string('request_time')->nullable(true);
            $table->integer('internet_id')->nullable(true);
            $table->integer('night')->nullable(true);
            $table->integer('profit')->nullable();
            $table->text('description')->nullable();
            $table->text('values')->nullable();
            $table->text('after_values')->nullable();
            $table->integer('callcenter_status')->default('0');
            $table->text('callcenter_desc')->nullable()->default(NULL);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('factors');
    }
}
