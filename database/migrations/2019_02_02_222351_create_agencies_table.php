<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateAgenciesTable extends Migration
{
    public function up()
    {
        Schema::create('agencies', function (Blueprint $table) {

		$table->integer('id',10)->unsigned();
		$table->integer('user_id')->nullable()->default(NULL);
        $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
		$table->integer('agency_type_id')->nullable()->default(NULL);
        $table->foreign('agency_type_id')->references('id')->on('agency_types')->onDelete('cascade');
		$table->string('name');
		$table->string('melicode')->nullable()->default(NULL);
		$table->string('phone');
		$table->string('call')->nullable()->default(NULL);
		$table->string('province')->nullable()->default(NULL);
		$table->string('city')->nullable()->default(NULL);
		$table->string('job')->nullable()->default(NULL);
		$table->text('extra_job')->nullable()->default(NULL);
		$table->text('extra')->nullable()->default(NULL);
		$table->text('location')->nullable()->default(NULL);
		$table->integer('status')->default('0');
		$table->text('description')->nullable()->default(NULL);
		$table->string('_token')->nullable()->default(NULL);
		$table->string('age')->nullable()->default(NULL);
		$table->integer('married')->nullable()->default(NULL);
		$table->string('education',200)->nullable()->default(NULL);
		$table->string('field_education',200)->nullable()->default(NULL);
		$table->string('address')->nullable()->default(NULL);
		$table->text('imgmeli')->nullable()->default(NULL);
		$table->text('imgturnover')->nullable()->default(NULL);
		$table->timestamp('expire_date')->nullable()->default(NULL);
		$table->text('values')->nullable()->default(NULL);

        $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
		$table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('agencies');
    }
}
