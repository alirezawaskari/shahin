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
        Schema::create('sms_campaigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ghorfe_online_list_id')->constrained('ghorfe_online_lists')->cascadeOnDelete();
            $table->enum('type', ['single', 'bulk', 'ai']);
            $table->foreignId('sms_message_template_id')->nullable()->constrained('sms_message_templates')->nullOnDelete();
            $table->foreignId('sender_number_id')->nullable()->constrained('sender_numbers')->nullOnDelete();
            $table->json('filters')->nullable();
            $table->string('access_tier_required')->nullable();
            $table->integer('total_recipients')->default(0);
            $table->integer('sent_count')->default(0);
            $table->integer('success_count')->default(0);
            $table->integer('failed_count')->default(0);
            $table->integer('pending_count')->default(0);

            // 'completed_with_errors' covers a campaign that stopped on
            // balance exhaustion with recipients unsent.
            $table->enum('status', ['draft', 'scheduled', 'sending', 'completed', 'completed_with_errors', 'failed'])
                ->default('draft');

            $table->timestamp('scheduled_at')->nullable();
            $table->decimal('cost_estimate', 12, 2)->nullable();
            $table->text('ai_goal_prompt')->nullable();
            $table->unsignedInteger('created_by');
            $table->foreign('created_by')->references('id')->on('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sms_campaigns');
    }
};
