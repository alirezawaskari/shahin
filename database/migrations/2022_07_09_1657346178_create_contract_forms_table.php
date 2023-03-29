<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContractFormsTable extends Migration
{
    public function up()
    {
        Schema::create('contract_forms', function (Blueprint $table) {

		$table->integer('id',10)->unsigned();
		$table->string('type');
		$table->string('placeholder')->nullable()->default(NULL);
		$table->string('name');
		$table->integer('required')->default('0');
		$table->string('value')->nullable()->default(NULL);
		$table->text('help')->nullable()->default(NULL);
		$table->string('level',100)->default('site');

        });
    }

    public function down()
    {
        Schema::dropIfExists('contract_forms');
    }
}
