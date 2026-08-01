<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The public branch page (پروفایل شعبه) the API serves to the branch frontend.
 *
 * `centers` held only the contract-side facts a branch was signed up with — title,
 * address, phone, contract number/date, an internal review note in `description`. The
 * branch page needs the customer-facing side: how to reach it, when it is open, and the
 * wording of its sections.
 *
 * NOT added here, because the data already exists and only needed a relation:
 *   - services            -> `centerables` morph (already wired in the admin)
 *   - staff               -> employees.center_id
 *   - financial plans     -> `financial_plans_type_items` morph, now also keyed by Center
 *   - faq                 -> `faqs`, gaining a center_id in the next migration
 *
 * Section titles are columns for the same reason as on the about page: "پزشکان شعبه" is
 * right for a clinic and wrong for a workshop. Null = the frontend's own default.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('centers', function (Blueprint $table) {
            // What to call this place in menus and headings. `description` is an internal
            // review note (see the admin label), so the public paragraph gets its own column.
            $table->string('label_type', 20)->nullable()->after('title');
            $table->text('intro_text')->nullable()->after('description');

            // contact block — `phone` and `address` already exist
            $table->string('email', 191)->nullable()->after('phone');
            $table->text('map_iframe_link')->nullable()->after('lon');

            // ساعات کاری, one entry per weekday: {"saturday":{"open":"08:00","close":"21:00","closed":false},...}
            // JSON rather than 7×2 columns: the frontend renders the whole week as one block
            // and nothing ever queries a single day.
            $table->json('working_hours')->nullable()->after('email');

            // editable section headings
            $table->string('about_title', 255)->nullable()->after('intro_text');
            $table->string('services_title', 255)->nullable()->after('about_title');
            $table->string('staff_title', 255)->nullable()->after('services_title');
            $table->string('plans_title', 255)->nullable()->after('staff_title');
        });
    }

    public function down(): void
    {
        Schema::table('centers', function (Blueprint $table) {
            $table->dropColumn([
                'label_type', 'intro_text', 'email', 'map_iframe_link', 'working_hours',
                'about_title', 'services_title', 'staff_title', 'plans_title',
            ]);
        });
    }
};
