<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('service_index_tariffs', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('service_id')->nullable();
            $table->foreign('service_id')->references('id')->on('services');
            $table->unsignedInteger('agent_id')->nullable();
            $table->foreign('agent_id')->references('id')->on('users');
            $table->string('title');
            $table->unsignedBigInteger('purchase_price');
            $table->softDeletes();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_index_tariffs');
    }
};
