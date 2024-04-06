<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateRepresentationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('representations', function (Blueprint $table) {
            $table->increments('id');
            $table->string('fa_name');
            $table->string('en_name');
            $table->string('slug');
            $table->mediumText('header_description')->nullable();
            $table->integer('status')->default(true);
            $table->string('sms_code')->nullable();
            $table->string('call')->nullable();
            $table->mediumText('address')->nullable();
            $table->float('lat')->default('35.84');
		    $table->float('lon')->default('51.8788');
            $table->mediumText('stamp')->nullable();
            $table->integer('percent')->default(10);

            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
		    $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->softDeletes();
        });

        Schema::create('representation_service', function (Blueprint $table) {
            $table->integer('service_id')->unsigned();
            $table->foreign('service_id')->references('id')->on('services')->onDelete('cascade');

            $table->integer('representation_id')->unsigned();
            $table->foreign('representation_id')->references('id')->on('representations')->onDelete('cascade');

            $table->primary(['service_id' , 'representation_id']);
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('representations');
    }
}
