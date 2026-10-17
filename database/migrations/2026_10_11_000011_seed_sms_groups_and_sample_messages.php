<?php

use App\Models\GhorfeOnlineList;
use App\Models\SenderNumber;
use App\Models\SmsMessage;
use App\Models\SmsMessageGroup;
use Database\Seeders\SmsMessageGroupSeeder;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Seed SMS message groups and sample messages for QA testing.
     */
    public function up(): void
    {
        (new SmsMessageGroupSeeder())->run();

        $businessA = GhorfeOnlineList::where('en_title', 'sms-test-business-a')->first();
        if (!$businessA) {
            return;
        }

        $senderNumber = $businessA->senderNumbers()->first() ?? SenderNumber::first();
        if (!$senderNumber) {
            return;
        }

        $maliGroup = SmsMessageGroup::where('slug', 'mali')->first();
        $poshtibaniGroup = SmsMessageGroup::where('slug', 'poshtibani')->first();

        // 1. Confidential message (Financial / Mali)
        if ($maliGroup && !SmsMessage::where('tracking_id', 'TRK-CONF-001')->exists()) {
            SmsMessage::create([
                'ghorfe_online_list_id' => $businessA->id,
                'direction' => 'outbound',
                'service_type' => 'advertising',
                'sender_number_id' => $senderNumber->id,
                'sender_phone' => $senderNumber->number,
                'recipient_phone' => '09121112233',
                'sms_message_group_id' => $maliGroup->id,
                'content' => 'مبلغ ۵,۰۰۰,۰۰۰ ریال به شماره شبا IR123456789 واریز شد (سند مالی محرمانه)',
                'character_count' => 70,
                'page_count' => 1,
                'cost' => 500,
                'status' => 'delivered',
                'tracking_id' => 'TRK-CONF-001',
            ]);
        }

        // 2. Normal non-confidential message (Support / Poshtibani)
        if ($poshtibaniGroup && !SmsMessage::where('tracking_id', 'TRK-NORM-002')->exists()) {
            SmsMessage::create([
                'ghorfe_online_list_id' => $businessA->id,
                'direction' => 'outbound',
                'service_type' => 'advertising',
                'sender_number_id' => $senderNumber->id,
                'sender_phone' => $senderNumber->number,
                'recipient_phone' => '09129998877',
                'sms_message_group_id' => $poshtibaniGroup->id,
                'content' => 'سلام، درخواست پشتیبانی شما با موفقیت ثبت شد.',
                'character_count' => 45,
                'page_count' => 1,
                'cost' => 500,
                'status' => 'delivered',
                'tracking_id' => 'TRK-NORM-002',
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        SmsMessage::whereIn('tracking_id', ['TRK-CONF-001', 'TRK-NORM-002'])->delete();
    }
};
