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
        Schema::create('percentage_allocation_employee_user_performance', function (Blueprint $table) {
            $table->foreignId('percentage_allocation_employee_id')->constrained();//percentage_allocation_employee_id
            $table->foreignId('user_performance_id')->constrained();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('percentage_allocation_employee_user_performance');
    }
};
