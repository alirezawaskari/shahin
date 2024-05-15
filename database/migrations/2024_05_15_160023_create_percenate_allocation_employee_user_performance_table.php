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
        Schema::create('percenate_allocation_employee_user_performance', function (Blueprint $table) {
            $table->foreignId('percenate_allocation_employee_id')->constrained();//percenate_allocation_employee_id
            $table->foreignId('user_performance_id')->constrained();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('percenate_allocation_employee_user_performance');
    }
};
