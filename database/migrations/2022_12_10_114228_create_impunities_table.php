<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateImpunitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('impunities', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('impunitiable_id');
            $table->string('impunitiable_type');
            $table->bigInteger('amount');
            $table->string('type',50);
            $table->text('description')->nullable();
            $table->tinyInteger('status')->default(1)->comment('0 = deActive 1 = active ');
            $table->unsignedInteger('created_by')->nullable()->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('impunities');
    }
}
