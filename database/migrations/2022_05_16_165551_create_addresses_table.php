<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateAddressesTable extends Migration
{
    public function up()
    {
        Schema::create('addresses', function (Blueprint $table) {

		$table->integer('id');
		$table->integer('user_id');
        $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
		$table->string('lat',191);
		$table->string('lon',191);
		$table->integer('province_id');
        $table->foreign('province_id')->references('id')->on('provinces')->onDelete('cascade');
		$table->integer('city_id');
        $table->foreign('city_id')->references('id')->on('cities')->onDelete('cascade');
		$table->integer('area_id')->nullable()->default(NULL);
        $table->foreign('area_id')->references('id')->on('areas')->onDelete('cascade');
		$table->text('address');
		$table->integer('plaque');
		$table->integer('unit')->nullable()->default(NULL);
		$table->string('postal_code',191);
		$table->string('recipient_name',191);
		$table->string('recipient_family',191);
		$table->string('recipient_mobile',191);

        $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
		$table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        $table->softDeletes();
        });

    }

    public function down()
    {
        Schema::dropIfExists('addresses');
    }
}
