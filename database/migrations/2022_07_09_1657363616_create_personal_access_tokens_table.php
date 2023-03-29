<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreatePersonalAccessTokensTable extends Migration
{
    public function up()
    {
        Schema::create('personal_access_tokens', function (Blueprint $table) {

		$table->bigInteger('id',20)->unsigned();
        $table->unsignedBigInteger('tokenable_id');
        $table->string('tokenable_type');//User Models
        $table->index(['tokenable_id', 'tokenable_type']);
		$table->string('name');
		$table->string('token',64);
		$table->text('abilities')->nullable()->default(NULL);
		$table->timestamp('last_used_at')->nullable()->default(NULL);

		$table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
		$table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('personal_access_tokens');
    }
}
