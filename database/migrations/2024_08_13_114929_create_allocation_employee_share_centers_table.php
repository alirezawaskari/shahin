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
        Schema::create('allocation_employee_share_centers', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('service_tariff_id')->nullable();
            $table->foreign('service_tariff_id')->references('id')->on('service_tariffs');
            $table->unsignedInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users');
            $table->unsignedInteger('agent_id')->nullable();
            $table->foreign('agent_id')->references('id')->on('users');
            $table->unsignedBigInteger('amount');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('allocation_employee_share_centers');
    }
};
