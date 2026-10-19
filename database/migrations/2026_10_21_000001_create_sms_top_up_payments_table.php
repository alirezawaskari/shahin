<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Pending/settled online payments that fund an SMS balance. The balance is
     * credited only when the payment module reports a verified success for a row.
     */
    public function up(): void
    {
        Schema::create('sms_top_up_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ghorfe_online_list_id')->constrained('ghorfe_online_lists')->cascadeOnDelete();
            $table->unsignedInteger('user_id')->nullable();
            $table->enum('credit_type', ['otp', 'advertising']);
            $table->decimal('amount', 12, 2);
            $table->enum('status', ['pending', 'paid', 'failed', 'cancelled'])->default('pending')->index();
            $table->string('gateway')->nullable();
            $table->string('authority')->nullable()->unique();
            $table->string('ref_number')->nullable()->unique();
            $table->text('return_url')->nullable();
            $table->foreignId('sms_transaction_id')->nullable()->constrained('sms_transactions')->nullOnDelete();
            $table->string('failure_reason')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sms_top_up_payments');
    }
};
