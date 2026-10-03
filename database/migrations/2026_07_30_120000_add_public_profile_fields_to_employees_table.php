<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The partner profile page (پروفایل همکار) the API serves to the partner frontend.
 *
 * `employees` was entirely contract-side — pay type, percentages, contract dates, document
 * images. Nothing on it described the person to a customer. description_one..three (added
 * with the about-us work) cover the three credential bullets; this adds the rest of what
 * the design puts above the fold.
 *
 * NOT added here, because the data exists and only needed a relation:
 *   - home branch -> employees.center_id
 *   - services    -> the employee_service pivot in the next migration
 *   - نمونه کارها -> `work_samples`, gaining an after_image collection on the model
 *   - نظرات       -> `ratings` (morph, already shared with Center)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            // The intro paragraph. resume_detail is a json blob of the internal hiring
            // resume, so it cannot double as the public bio.
            $table->text('bio')->nullable()->after('description_three');

            // A free-form rich-text block edited with CKEditor, for whatever the business
            // type needs under the intro — a clinic puts its شماره نظام پزشکی here, a law
            // firm its bar number, a workshop its certifications. Deliberately NOT a
            // dedicated licence-number column: that would only fit medical partners.
            $table->longText('extra_content')->nullable()->after('bio');

            // "ساعات حضور در مستاجرین و شرکای تجاری": one line each, exactly as the design renders them
            // ("شنبه تا دوشنبه" / "۱۰ صبح تا ۱۰ شب"). Per-weekday hours belong to the
            // branch (centers.working_hours), not to the person visiting it.
            $table->string('attendance_days', 255)->nullable()->after('extra_content');
            $table->string('attendance_hours', 255)->nullable()->after('attendance_days');

            // editable section headings, same reason as on the about/branch pages
            $table->string('services_title', 255)->nullable()->after('attendance_hours');
            $table->string('portfolio_title', 255)->nullable()->after('services_title');
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn([
                'bio', 'extra_content', 'attendance_days', 'attendance_hours',
                'services_title', 'portfolio_title',
            ]);
        });
    }
};
