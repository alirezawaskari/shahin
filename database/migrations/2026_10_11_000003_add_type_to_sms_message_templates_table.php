<?php

use App\Models\SmsMessageTemplate;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Mirrors a real SMS-gateway panel's template shape: a `type`
     * distinguishing a confidential template (مالی/حقوقی group) from a
     * normal one. It is NEVER set from a form field — SmsMessageTemplate's
     * saving-event observer always (re)computes it from the linked
     * sms_message_group_id, the same way SmsMessage::isConfidential()
     * derives confidentiality from its group.
     */
    public function up(): void
    {
        Schema::table('sms_message_templates', function (Blueprint $table) {
            $table->enum('type', ['normal', 'confidential'])->default('normal')->after('sms_message_group_id');
        });

        // Backfill existing rows using the real model logic (walks the
        // group's parent chain, same as SmsMessage::isConfidential()) rather
        // than a best-effort SQL LIKE match.
        SmsMessageTemplate::withTrashed()->with('group')->get()->each(function (SmsMessageTemplate $template) {
            $template->timestamps = false;
            $template->type = $template->group?->isConfidential() ? 'confidential' : 'normal';
            $template->saveQuietly();
        });
    }

    public function down(): void
    {
        Schema::table('sms_message_templates', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
