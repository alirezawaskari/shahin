<?php

use App\Models\Groupsms;
use App\Models\SmsMessage;
use App\Models\SmsMessageTemplate;
use App\Models\SmsTemplate;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Sync all existing sms_messages into legacy groupsms table
        foreach (SmsMessage::all() as $msg) {
            Groupsms::updateOrCreate(
                [
                    'message' => $msg->content,
                    'phones' => $msg->recipient_phone,
                ],
                [
                    'title' => $msg->service_type === 'otp' ? 'پیامک اعتبارسنجی' : ($msg->service_type === 'advertising' ? 'پیامک تبلیغاتی' : 'پیامک تکی'),
                    'type' => 'single',
                    'status' => in_array($msg->status, ['delivered', 'sent'], true) ? 2 : ($msg->status === 'failed' ? 0 : 1),
                    'flag' => true,
                    'send_at' => $msg->created_at,
                    'created_at' => $msg->created_at,
                    'updated_at' => $msg->updated_at,
                ]
            );
        }

        // 2. Sync all existing legacy sms_templates into sms_message_templates
        foreach (SmsTemplate::all() as $oldTpl) {
            SmsMessageTemplate::updateOrCreate(
                ['content' => $oldTpl->content],
                [
                    'title' => $oldTpl->name,
                    'type' => 'normal',
                    'service_type' => 'advertising',
                    'status' => 'active',
                    'created_by' => $oldTpl->user_id,
                ]
            );
        }

        // 3. Sync all sms_message_templates into legacy sms_templates
        foreach (SmsMessageTemplate::all() as $newTpl) {
            SmsTemplate::updateOrCreate(
                ['content' => $newTpl->content],
                [
                    'name' => $newTpl->title,
                    'fa_name' => $newTpl->title,
                    'status' => $newTpl->status === 'active' ? 1 : 0,
                    'user_id' => $newTpl->created_by,
                ]
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Non-destructive
    }
};
