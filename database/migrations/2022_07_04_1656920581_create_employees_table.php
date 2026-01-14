<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateEmployeesTable extends Migration
{
    public function up()
    {
        Schema::create('employees', function (Blueprint $table) {

		$table->integer('id');
		$table->integer('user_id');
        $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        $table->integer('parent_id')->nullable();
        $table->foreign('parent_id')->references('id')->on('employees')->onDelete('cascade');
		$table->integer('center_id')->nullable()->default(NULL);
        $table->foreign('center_id')->references('id')->on('centers')->onDelete('cascade');
		$table->integer('status')->default('1');
        $table->string('business_phone')->default('02191095745');
		$table->integer('show_status')->default('1');
        $table->integer('sales_commitment')->nullable()->default(NULL);
		$table->integer('tax')->default('10');
		$table->integer('percent')->default('50');
		$table->bigInteger('guarantee')->default('0');
		$table->string('pay_type',110)->default('pay_end_work');
		$table->string('shaba_number')->nullable()->default(NULL);
        $table->string('card_number')->nullable()->default(NULL);
		$table->string('work_title',110)->nullable()->default(NULL);
		$table->integer('blocked_percentage')->default('5');
        $table->timestamp('data_access_date')->default(DB::raw('CURRENT_TIMESTAMP'));
		$table->timestamp('contract_start_date')->default(DB::raw('CURRENT_TIMESTAMP'));
		$table->timestamp('contract_expiration_date')->default(DB::raw('CURRENT_TIMESTAMP'));
		$table->string('contract_number',110)->nullable()->default(NULL);
		$table->boolean('is_terms_accept',110)->nullable()->default(NULL);
        $table->text('imgaccess')->nullable();
        $table->text('imglastcertificate')->nullable();
        $table->text('resume_detail')->nullable();
        $table->text('contract')->nullable();
        $table->integer('contract_status')->default(0);

		$table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
		$table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('employees');
    }
}
