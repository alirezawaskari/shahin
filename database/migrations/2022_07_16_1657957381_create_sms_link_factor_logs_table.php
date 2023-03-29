<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateSmsLinkFactorLogsTable extends Migration
{
    public function up()
    {
        //save the log of send sms
        Schema::create('sms_link_factor_logs', function (Blueprint $table) {

		$table->integer('id');
		$table->string('phone');
		$table->integer('type')->default('0');//type == 0 => controller && type == 1 => event

		$table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
		$table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('sms_link_factor_logs');
    }
}
