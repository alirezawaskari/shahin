<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateOrganizationsTable extends Migration
{
    public function up()
    {
        Schema::create('organizations', function (Blueprint $table) {

		$table->integer('id')->unsigned();
		$table->integer('parent_id')->unsigned()->nullable();
        $table->string('archive_number' , 110);
		$table->string('organ_code',110)->nullable()->default(NULL);
		$table->integer('precent')->nullable()->default(NULL);
		$table->string('fa_name');
		$table->string('en_name')->nullable()->default(NULL);
		$table->bigInteger('credit')->nullable()->default(NULL);
		$table->string('agent_organ')->nullable()->default(NULL);
		$table->string('slug');
		$table->text('header_description')->nullable()->default(NULL);
		$table->integer('status')->default('1');
        $table->integer('archive_status')->default('1');
		$table->string('type')->default('contracting_party');
		$table->text('image')->nullable()->default(NULL);
		$table->text('document')->nullable()->default(NULL);
		$table->string('sms_code')->nullable()->default(NULL);
		$table->string('call')->nullable()->default(NULL);
		$table->text('address')->nullable()->default(NULL);
		$table->text('stamp')->nullable()->default(NULL);

        $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
		$table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        $table->softDeletes();
        });

        Schema::create('organization_service', function (Blueprint $table) {
            $table->integer('service_id')->unsigned();
            $table->foreign('service_id')->references('id')->on('services')->onDelete('cascade');

            $table->integer('organization_id')->unsigned();
            $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade');

            $table->primary(['service_id' , 'organization_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('organizations');
    }
}
