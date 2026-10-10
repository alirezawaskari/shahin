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
        Schema::create('sms_message_templates', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('content');
            $table->foreignId('sms_message_group_id')->nullable()->constrained('sms_message_groups')->nullOnDelete();

            // Mirrors a real SMS-gateway panel's template shape: a `type`
            // distinguishing a confidential template (مالی/حقوقی group) from
            // a normal one. It is NEVER set from a form field —
            // SmsMessageTemplateObserver always (re)computes it from the
            // linked sms_message_group_id, the same way
            // SmsMessage::isConfidential() derives confidentiality from its
            // group.
            $table->enum('type', ['normal', 'confidential'])->default('normal')->after('sms_message_group_id');

            $table->enum('service_type', ['otp', 'normal', 'advertising'])->default('normal');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->unsignedInteger('created_by');
            $table->foreign('created_by')->references('id')->on('users')->cascadeOnDelete();
            $table->unsignedInteger('updated_by')->nullable();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sms_message_templates');
    }
};
