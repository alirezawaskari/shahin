<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Per-business SMS auto-responder rules («پاسخ‌گوی خودکار»): an inbound SMS whose text matches a rule's
 * trigger keyword is answered automatically through the normal single-send path.
 *
 * - normalized_keyword holds the trigger after SmsKeywordNormalizer (trim, case-fold, Latin digits,
 *   collapsed whitespace) so collision checks and the engine compare the same canonical form.
 * - sms_messages.auto_responder_id marks an outbound message as an auto-reply (and which rule produced it);
 *   it is also what the per-sender rate-limit window counts. Plain indexed soft reference, no FK:
 *   rules are soft-deleted and messages must outlive them.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sms_auto_responders', function (Blueprint $t) {
            $t->id();
            $t->foreignId('ghorfe_online_list_id')->constrained('ghorfe_online_lists')->cascadeOnDelete();
            $t->string('name', 191);
            $t->string('trigger_keyword', 191);
            $t->string('normalized_keyword', 191)->index();
            $t->string('match_type', 20)->default('exact'); // exact | starts_with
            $t->text('reply_text');
            $t->unsignedBigInteger('sender_number_id')->nullable();
            $t->boolean('is_active')->default(true);
            $t->unsignedInteger('created_by')->nullable();
            $t->unsignedInteger('updated_by')->nullable();
            $t->timestamps();
            $t->softDeletes();

            $t->index(['ghorfe_online_list_id', 'is_active']);
        });

        Schema::table('sms_messages', function (Blueprint $t) {
            $t->unsignedBigInteger('auto_responder_id')->nullable()->index();
        });
    }

    public function down(): void
    {
        Schema::table('sms_messages', function (Blueprint $t) {
            $t->dropIndex(['auto_responder_id']);
            $t->dropColumn('auto_responder_id');
        });

        Schema::dropIfExists('sms_auto_responders');
    }
};
