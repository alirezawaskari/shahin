<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('percentage_allocation_employees', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('percentage_allocation_employeeable_id')->nullable();
            $table->string('percentage_allocation_employeeable_type',100)->nullable();//Service Models || Center Models
            $table->unsignedInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users');
            $table->unsignedInteger('agent_id')->nullable();
            $table->foreign('agent_id')->references('id')->on('users');
            $table->string('type');
            $table->string('factor_sender')->default('all');//all || employee_factors
            $table->string('limit_type')->nullable();//null || count || price
            $table->integer('min_limit_type')->nullable();
            $table->integer('max_limit_type')->nullable();
            $table->integer('percent');
            $table->timestamp('start_allocation_date')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('end_allocation_date')->default(DB::raw('CURRENT_TIMESTAMP'));

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('percentage_allocation_employees');
    }
};
