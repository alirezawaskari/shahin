<?php

use App\Models\Center;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Branch questions move from `faqs.center_id` to the `asked_questions` morph.
 *
 * 2026_07_30_110100 put them in `faqs` to avoid a second question table. `asked_questions`
 * turned out to BE that table already — Service, ServiceItem and GhorfeOnlineList have used
 * it since 2022 — so a branch now uses the same morph as everything else, and `faqs` goes
 * back to being only the multilingual site/business FAQ it was built for.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('faqs', 'center_id')) {
            return;
        }

        $centerType = (new Center())->getMorphClass();
        $now        = now();

        DB::table('faqs')->whereNotNull('center_id')->orderBy('id')->each(function ($faq) use ($centerType, $now) {
            DB::table('asked_questions')->insert([
                'askedquestionable_id'   => $faq->center_id,
                'askedquestionable_type' => $centerType,
                'question'               => (string) $faq->title,
                // `answer` is NOT NULL; an unanswered question keeps an empty string
                'answer'                 => (string) $faq->text,
                'created_at'             => $now,
                'updated_at'             => $now,
            ]);
        });

        DB::table('faqs')->whereNotNull('center_id')->delete();

        Schema::table('faqs', function (Blueprint $table) {
            $table->dropForeign('faqs_center_id_foreign');
            $table->dropColumn('center_id');
        });
    }

    /**
     * Puts the column back and returns the branch questions to it. Only rows that are still
     * live are moved back — a question soft-deleted while in `asked_questions` was deleted
     * by an admin, and `faqs` has no deleted_at to express that.
     */
    public function down(): void
    {
        Schema::table('faqs', function (Blueprint $table) {
            $table->unsignedInteger('center_id')->nullable()->after('ghorfe_online_list_id');

            $table->foreign('center_id')
                ->references('id')->on('centers')
                ->onDelete('cascade');
        });

        $centerType = (new Center())->getMorphClass();

        DB::table('asked_questions')
            ->where('askedquestionable_type', $centerType)
            ->whereNull('deleted_at')
            ->orderBy('id')
            ->each(function ($asked) {
                DB::table('faqs')->insert([
                    'center_id' => $asked->askedquestionable_id,
                    'title'     => $asked->question,
                    'text'      => $asked->answer,
                ]);
            });

        DB::table('asked_questions')->where('askedquestionable_type', $centerType)->delete();
    }
};
