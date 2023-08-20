<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('service_id')->unsigned()->nullable();
            $table->foreign('service_id')->references('id')->on('services');
            $table->string('sms_code')->nullable()->default(NULL);
            $table->string('name')->nullable(true);
            $table->string('family')->nullable(true);
            $table->string('father')->nullable(true);
            $table->string('phone')->unique();
            $table->text('avatar');
            $table->string('melicode')->nullable(true);
            $table->string('birth_year')->nullable(true);
            $table->string('birth_month')->nullable(true);
            $table->string('birth_day')->nullable(true);
            $table->string('province_id')->nullable(true);
            $table->foreign('province_id')->references('id')->on('provinces');
            $table->string('city_id')->nullable(true);
            $table->foreign('city_id')->references('id')->on('cities');
            $table->string('area_id')->nullable(true);
            $table->foreign('area_id')->references('id')->on('areas');
            $table->string('password');
            $table->string('call')->nullable(true);
            $table->string('shenasnamecode')->nullable(true);
            $table->string('work_address')->nullable()->default(NULL);
		    $table->string('work_phone',110)->nullable()->default(NULL);
		    $table->string('home_phone',110)->nullable()->default(NULL);
            $table->string('serialcharacter')->nullable(true);
            $table->string('serialtop')->nullable(true);
            $table->string('serialbottom')->nullable(true);
            $table->string('issuing')->nullable(true);
            $table->string('postalcode')->nullable(true);
            $table->string('address')->nullable(true);
            $table->string('email')->nullable(true);
            $table->integer('sex')->default(0);
            $table->text('imgmeli')->nullable(true);
            $table->text('imgcatch')->nullable()->default(NULL);
		    $table->text('imglease')->nullable()->default(NULL);
		    $table->text('imgczech')->nullable()->default(NULL);
            $table->text('imgshenasname')->nullable(true);
            $table->text('imgsignature')->nullable(true);
            $table->text('imgturnover')->nullable()->default(NULL);
            $table->string('level')->default('user');
            $table->boolean('active')->default(true);
            $table->boolean('confirm')->default(true);
            $table->string('token')->unique();
            $table->string('work')->nullable()->default(NULL);
            $table->string('emergency_call')->nullable()->default(NULL);
            $table->integer('center_id')->nullable()->default(NULL);
            $table->foreign('center_id')->references('id')->on('centers');
            $table->string('married')->nullable()->default(NULL);
            $table->string('identifier_code')->nullable()->default(NULL);
            $table->string('api_token')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('activation_code')->nullable(true);
            $table->string('lalecard_code',100)->nullable()->default(NULL);
            $table->unsignedBigInteger('wallet')->default(0);
            $table->unsignedBigInteger('credit')->default(0);
            $table->integer('work_status')->default(0);
            $table->integer('limit_count')->default(0);
            $table->integer('callcenter_id')->nullable()->unsigned();
            $table->foreign('callcenter_id')->references('id')->on('users');
            $table->integer('representation_id')->nullable()->unsigned();
            $table->foreign('representation_id')->references('id')->on('representations');
            $table->integer('admin_representation_id')->nullable()->unsigned();
            $table->foreign('admin_representation_id')->references('id')->on('representations');
            $table->integer('organization_id')->nullable()->unsigned();
            $table->foreign('organization_id')->references('id')->on('organizations');
            $table->integer('admin_organization_id')->nullable()->unsigned();
            $table->foreign('admin_organization_id')->references('id')->on('organizations');
            $table->integer('user_code')->nullable()->default(NULL);
            $table->string('type_employ',110)->nullable()->default(NULL);
            $table->string('part')->nullable()->default(NULL);
            $table->integer('lalecard')->default('0');
            $table->string('shaba_number',110)->nullable()->default(NULL);
            $table->string('conduct',100)->nullable();
            $table->rememberToken();

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
        Schema::dropIfExists('users');
    }
}
