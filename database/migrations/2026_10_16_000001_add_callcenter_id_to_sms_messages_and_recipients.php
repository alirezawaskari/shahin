<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Lets a sent SMS remember WHICH request (callcenters row) it was sent for, so the request's
 * details pop-up can show that request's own message history. Plain indexed soft reference,
 * like sms_messages.request_id: requests are soft-deleted and must never block or cascade.
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach (['sms_messages', 'sms_campaign_recipients'] as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->unsignedInteger('callcenter_id')->nullable()->index();
            });
        }
    }

    public function down(): void
    {
        foreach (['sms_messages', 'sms_campaign_recipients'] as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->dropIndex(['callcenter_id']);
                $t->dropColumn('callcenter_id');
            });
        }
    }
};
