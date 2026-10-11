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
        Schema::create('sms_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ghorfe_online_list_id')->nullable()->constrained('ghorfe_online_lists')->nullOnDelete();
            $table->enum('direction', ['outbound', 'inbound'])->default('outbound');
            $table->enum('service_type', ['otp', 'normal', 'advertising'])->default('normal');
            $table->foreignId('sender_number_id')->nullable()->constrained('sender_numbers')->nullOnDelete();
            $table->string('recipient_phone')->nullable();
            $table->string('sender_phone')->nullable();
            $table->foreignId('sms_message_template_id')->nullable()->constrained('sms_message_templates')->nullOnDelete();
            $table->foreignId('sms_message_group_id')->nullable()->constrained('sms_message_groups')->nullOnDelete();
            $table->text('content');
            $table->integer('character_count')->default(0);
            $table->integer('page_count')->default(1);
            $table->string('status')->default('queued');
            $table->string('delivery_status')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->decimal('cost', 12, 2)->nullable();
            $table->string('tracking_id')->nullable()->index();
            $table->text('error_reason')->nullable();
            $table->unsignedInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            // requests.id is a plain signed `integer` (not unsigned, see
            // 2022_07_09_1657367827_create_requests_table.php), so this FK
            // column must stay signed `integer` to match it exactly.
            // No DB-level FK to `requests`: that table's `id` column is a
            // pre-existing legacy wart (not a proper unsigned/indexed primary
            // key), which makes MySQL reject a real foreign key constraint
            // here (errno 150). Kept as a plain soft reference, consistent
            // with how the rest of the app treats this column elsewhere.
            $table->integer('request_id')->nullable()->index();
            $table->boolean('is_bulk')->default(false);
            $table->foreignId('sms_campaign_id')->nullable()->constrained('sms_campaigns')->nullOnDelete();
            $table->unsignedInteger('created_by')->nullable();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->timestamps();

            // Supports per-business, time-ordered lookups (message logs,
            // report ranges) without scanning the whole table.
            $table->index(['ghorfe_online_list_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sms_messages');
    }
};
