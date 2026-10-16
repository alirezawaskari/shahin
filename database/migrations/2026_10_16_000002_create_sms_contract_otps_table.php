<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * One-time codes that verify WHO is signing the SMS usage contract + telecom rules.
 *
 * A code is bound to the signer, the business, the exact contract/rules text (hash) and the
 * typed signature name, and is stored only as an HMAC. The business row additionally keeps the
 * evidence of the verified signature: the phone the code went to, when, and the text hash.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sms_contract_otps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ghorfe_online_list_id')->constrained('ghorfe_online_lists')->cascadeOnDelete();
            $table->unsignedInteger('user_id');
            $table->string('phone', 20);
            $table->string('code_hash', 64);
            $table->string('contract_hash', 64);
            $table->string('signature_name_hash', 64);
            $table->unsignedSmallInteger('attempts')->default(0);
            $table->timestamp('expires_at');
            $table->timestamp('used_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'ghorfe_online_list_id', 'used_at']);
            $table->index(['user_id', 'created_at']);
        });

        Schema::table('ghorfe_online_lists', function (Blueprint $table) {
            $table->string('sms_contract_signed_phone', 20)->nullable()->after('sms_contract_signature_name');
            $table->string('sms_contract_text_hash', 64)->nullable()->after('sms_contract_signed_phone');
            $table->timestamp('sms_contract_otp_verified_at')->nullable()->after('sms_contract_text_hash');
        });
    }

    public function down(): void
    {
        Schema::table('ghorfe_online_lists', function (Blueprint $table) {
            $table->dropColumn(['sms_contract_signed_phone', 'sms_contract_text_hash', 'sms_contract_otp_verified_at']);
        });

        Schema::dropIfExists('sms_contract_otps');
    }
};
