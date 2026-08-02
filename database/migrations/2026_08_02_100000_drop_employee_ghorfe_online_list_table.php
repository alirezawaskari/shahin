<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Drop the curated "همکاران متخصص" pivot.
 *
 * 2026_07_30_100200 created it on the premise that a business features a hand-picked subset
 * of its staff. It doesn't: the about-us page shows the employees of the business's own
 * branches, which `employees.center_id` already answers. Keeping both meant two lists that
 * could disagree — an employee could be featured by a business they no longer work for, and
 * a new hire had to be added twice.
 *
 * GhorfeOnlineList::staff() replaces the relation this table backed.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('employee_ghorfe_online_list');
    }

    /**
     * Recreated exactly as 2026_07_30_100200 left it, foreign keys included. The rows are
     * not restored: the featured list is derived from employees.center_id now, so there is
     * no source to rebuild them from.
     */
    public function down(): void
    {
        Schema::create('employee_ghorfe_online_list', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ghorfe_online_list_id');
            // employees.id is a SIGNED `int`, so this must be integer() to match.
            $table->integer('employee_id');
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
};
