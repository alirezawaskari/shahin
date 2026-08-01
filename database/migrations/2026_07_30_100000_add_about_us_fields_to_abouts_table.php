<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The "درباره ما" page the API serves to the about-us frontend.
 *
 * `abouts` only had the legacy five-box content of the OLD lalecard site (content,
 * content2, content_one..content_five + four box images). The new page is a fixed set of
 * sections — hero, business info, virtual tour, branches, specialists, "why us" stats,
 * goals, faq — so each gets its own column instead of being squeezed into the numbered
 * boxes, which carry no meaning the frontend can key off.
 *
 * Section TITLES are columns too, not constants: the same page is rendered for a clinic
 * ("پزشکان ما را بشناسید" / "تور مجازی مطب") and for an online shop, where those words are
 * wrong. Null = the frontend falls back to its default for that section.
 *
 * Branch list, specialist staff and the FAQ are relations, not columns — see the
 * center_ghorfe_online_list / employee_ghorfe_online_list migrations and `faqs`.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('abouts', function (Blueprint $table) {
            // hero: main image is a Spatie collection (main_image), not a column
            $table->text('intro_text')->nullable()->after('content_five');

            // business information block
            $table->string('established_year', 10)->nullable()->after('intro_text');
            $table->string('contact_phone', 30)->nullable()->after('established_year');
            $table->text('address')->nullable()->after('contact_phone');
            $table->string('working_days', 255)->nullable()->after('address');
            $table->string('working_hours', 255)->nullable()->after('working_days');

            // video / 360° virtual tour — optional section (an online business has no venue).
            // Phase one is a placeholder video: either an uploaded file (tour_video
            // collection) or an external link; tour_poster is the still shown before play.
            $table->string('tour_title', 255)->nullable()->after('working_hours');
            $table->text('tour_video_link')->nullable()->after('tour_title');
            $table->string('tour_description', 500)->nullable()->after('tour_video_link');

            // section headings for the two relation-backed blocks
            $table->string('branches_title', 255)->nullable()->after('tour_description');
            $table->string('staff_title', 255)->nullable()->after('branches_title');

            // "چرا {کسب و کار}؟" — three stat tiles. The labels are editable because the
            // unit differs per business type (بیمار راضی vs مشتری راضی).
            $table->string('why_title', 255)->nullable()->after('staff_title');
            $table->unsignedSmallInteger('years_experience')->nullable()->after('why_title');
            $table->unsignedInteger('satisfied_customers')->nullable()->after('years_experience');
            $table->decimal('average_rating', 3, 2)->nullable()->after('satisfied_customers');
            $table->string('stat_experience_label', 100)->nullable()->after('average_rating');
            $table->string('stat_customers_label', 100)->nullable()->after('stat_experience_label');
            $table->string('stat_rating_label', 100)->nullable()->after('stat_customers_label');

            // goals / vision (image is the goals_image Spatie collection)
            $table->string('goals_title', 255)->nullable()->after('stat_rating_label');
            $table->text('goals_text')->nullable()->after('goals_title');
        });
    }

    public function down(): void
    {
        Schema::table('abouts', function (Blueprint $table) {
            $table->dropColumn([
                'intro_text',
                'established_year', 'contact_phone', 'address', 'working_days', 'working_hours',
                'tour_title', 'tour_video_link', 'tour_description',
                'branches_title', 'staff_title',
                'why_title', 'years_experience', 'satisfied_customers', 'average_rating',
                'stat_experience_label', 'stat_customers_label', 'stat_rating_label',
                'goals_title', 'goals_text',
            ]);
        });
    }
};
