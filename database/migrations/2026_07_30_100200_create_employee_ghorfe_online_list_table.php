<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * "همکاران متخصص" — the specialist staff a business shows on its about-us page.
 *
 * An employee already belongs to exactly ONE branch (employees.center_id), and that stays
 * the employee's home branch. This is a different question: which employees does the
 * BUSINESS feature as its specialists. It is a curated list, not the full staff roster —
 * an employee of a branch is not automatically shown, and a business can feature someone
 * whose branch it shares with another business. Hence a pivot, not a column.
 *
 * employees.id is a SIGNED `int` (not unsigned like most tables here), so the FK column
 * has to be integer() — an unsignedInteger would be rejected on the type mismatch.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_ghorfe_online_list', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ghorfe_online_list_id');
            $table->integer('employee_id');
            // display order of the staff cards on the about-us page
            $table->unsignedSmallInteger('sort')->nullable();

            $table->unique(['ghorfe_online_list_id', 'employee_id'], 'employee_ghorfe_unique');

            $table->foreign('ghorfe_online_list_id')
                ->references('id')->on('ghorfe_online_lists')
                ->onDelete('cascade');
            $table->foreign('employee_id')
                ->references('id')->on('employees')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_ghorfe_online_list');
    }
};
