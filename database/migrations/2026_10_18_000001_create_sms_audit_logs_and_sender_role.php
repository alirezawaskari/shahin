<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Central audit trail for every outgoing SMS (single, bulk, OTP), kept apart from the generic
 * log_messages table so it can be indexed and filtered by sender/business. All references are plain
 * indexed soft references: audit rows must outlive (and never block) the rows they describe.
 * sms_messages itself also remembers the sender's role at send time.
 * (sms_messages.request_id already exists; bulk rows carry their request in callcenter_id.)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sms_audit_logs', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('sms_message_id')->nullable()->index();
            $t->unsignedBigInteger('sms_campaign_id')->nullable()->index();
            $t->unsignedBigInteger('ghorfe_online_list_id')->nullable()->index();
            $t->unsignedBigInteger('sender_id')->nullable()->index();
            $t->string('sender_role', 64)->nullable();
            $t->string('access_tier', 32)->nullable();
            $t->unsignedInteger('request_id')->nullable()->index();
            $t->unsignedInteger('callcenter_id')->nullable()->index();
            $t->string('channel', 32);
            $t->string('service_type', 32)->nullable();
            $t->timestamp('sent_at')->nullable();
            $t->timestamps();
        });

        Schema::table('sms_messages', function (Blueprint $t) {
            $t->string('sender_role', 64)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('sms_messages', function (Blueprint $t) {
            $t->dropColumn('sender_role');
        });

        Schema::dropIfExists('sms_audit_logs');
    }
};
