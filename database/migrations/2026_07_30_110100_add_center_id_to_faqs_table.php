<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The branch page has its own "سوالات متداول" block, so a question has to be able to
 * belong to a center and not only to a business.
 *
 * Reuses `faqs` rather than adding a second question table — the columns (title/text plus
 * the three translations) are exactly the same, and the API already reads FAQs from here.
 *
 * Scoping stays simple because the two keys are mutually exclusive:
 *   center_id set            -> a branch question
 *   ghorfe_online_list_id set-> a business question
 *   both null                -> the main lalecard site (what /faq renders)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('faqs', function (Blueprint $table) {
            $table->unsignedInteger('center_id')->nullable()->after('ghorfe_online_list_id');

            $table->foreign('center_id')
                ->references('id')->on('centers')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('faqs', function (Blueprint $table) {
            $table->dropForeign('faqs_center_id_foreign');
            $table->dropColumn('center_id');
        });
    }
};
