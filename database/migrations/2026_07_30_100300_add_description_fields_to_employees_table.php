<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The three descriptive lines shown under a specialist's name.
 *
 * The about-us spec calls these "the three descriptive text fields already available in
 * the Employees table" — they were NOT there. `employees` had only work_title (the job
 * title) and resume_detail (a single free-text resume blob); neither can hold the three
 * separate credential lines the partner-profile design lists under the doctor's name
 * (degree, specialty, association membership).
 *
 * Kept as three discrete columns rather than one list so the frontend can render them as
 * fixed bullet slots without parsing.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->text('description_one')->nullable()->after('resume_detail');
            $table->text('description_two')->nullable()->after('description_one');
            $table->text('description_three')->nullable()->after('description_two');
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['description_one', 'description_two', 'description_three']);
        });
    }
};
