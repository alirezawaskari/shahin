<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * task.md §2-6 requires a one-time usage agreement (تعهدنامه) a business
     * must sign — checkbox + a recorded signature — before any SMS feature
     * is usable. These columns back that acceptance; EnsureSmsContractSigned
     * middleware reads `sms_contract_accepted` to gate the module.
     */
    public function up(): void
    {
        Schema::table('ghorfe_online_lists', function (Blueprint $table) {
            $table->boolean('sms_contract_accepted')->default(false)->after('id');
            $table->timestamp('sms_contract_accepted_at')->nullable()->after('sms_contract_accepted');
            $table->string('sms_contract_signature_name')->nullable()->after('sms_contract_accepted_at');
            $table->unsignedInteger('sms_contract_accepted_by')->nullable()->after('sms_contract_signature_name');
            $table->foreign('sms_contract_accepted_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('ghorfe_online_lists', function (Blueprint $table) {
            $table->dropForeign(['sms_contract_accepted_by']);
            $table->dropColumn([
                'sms_contract_accepted',
                'sms_contract_accepted_at',
                'sms_contract_signature_name',
                'sms_contract_accepted_by',
            ]);
        });
    }
};
