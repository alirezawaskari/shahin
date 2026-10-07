<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sms_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ghorfe_online_list_id')->constrained('ghorfe_online_lists')->cascadeOnDelete();
            $table->enum('credit_type', ['otp', 'advertising']);
            $table->foreignId('sender_number_id')->nullable()->constrained('sender_numbers')->nullOnDelete();
            $table->decimal('charge_amount', 12, 2)->default(0);
            $table->decimal('consume_amount', 12, 2)->default(0);
            $table->decimal('balance_before', 12, 2);
            $table->decimal('balance_after', 12, 2);
            $table->foreignId('sms_message_id')->nullable()->constrained('sms_messages')->nullOnDelete();
            $table->foreignId('sms_campaign_id')->nullable()->constrained('sms_campaigns')->nullOnDelete();
            $table->string('tracking_id')->nullable()->index();
            $table->unsignedInteger('created_by')->nullable();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sms_transactions');
    }
};
