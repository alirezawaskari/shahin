<?php

use App\Models\GhorfeOnlineList;
use App\Models\SenderNumber;
use App\Models\SmsMessage;
use App\Models\SmsMessageGroup;
use App\Models\SmsMessageTemplate;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $business = GhorfeOnlineList::first();
        if (!$business) {
            return;
        }

        $sender = $business->senderNumbers()->first() ?? SenderNumber::first();
        if (!$sender) {
            return;
        }

        $otpSender = SenderNumber::where('type', 'otp')->first() ?? $sender;

        $maliGroup = SmsMessageGroup::where('slug', 'mali')->first();
        $supportGroup = SmsMessageGroup::where('slug', 'poshtibani')->first();
        $salesGroup = SmsMessageGroup::where('name', 'like', '%فروش%')->first();

        $otpTemplate = SmsMessageTemplate::where('service_type', 'otp')->first();
        $invoiceTemplate = SmsMessageTemplate::where('title', 'like', '%فاکتور%')->first();
        $advTemplate = SmsMessageTemplate::where('service_type', 'advertising')->first();

        $samples = [
            // 1. OTP Delivered
            [
                'ghorfe_online_list_id' => $business->id,
                'direction' => 'outbound',
                'service_type' => 'otp',
                'sender_number_id' => $otpSender->id,
                'sender_phone' => $otpSender->number,
                'recipient_phone' => '09123456789',
                'sms_message_template_id' => $otpTemplate?->id,
                'sms_message_group_id' => null,
                'content' => 'کد تایید ورود شما: 482915\nاین کد به مدت ۲ دقیقه معتبر است.',
                'character_count' => 62,
                'page_count' => 1,
                'cost' => 600,
                'status' => 'delivered',
                'tracking_id' => 'TRK-OTP-101',
                'created_at' => now()->subHours(2),
                'updated_at' => now()->subHours(2),
            ],
            // 2. OTP Delivered
            [
                'ghorfe_online_list_id' => $business->id,
                'direction' => 'outbound',
                'service_type' => 'otp',
                'sender_number_id' => $otpSender->id,
                'sender_phone' => $otpSender->number,
                'recipient_phone' => '09351234567',
                'sms_message_template_id' => $otpTemplate?->id,
                'sms_message_group_id' => null,
                'content' => 'کد تایید پرداخت شما: 918234\nپلتفرم آنلاین شاهین',
                'character_count' => 54,
                'page_count' => 1,
                'cost' => 600,
                'status' => 'delivered',
                'tracking_id' => 'TRK-OTP-102',
                'created_at' => now()->subHours(5),
                'updated_at' => now()->subHours(5),
            ],
            // 3. OTP Failed
            [
                'ghorfe_online_list_id' => $business->id,
                'direction' => 'outbound',
                'service_type' => 'otp',
                'sender_number_id' => $otpSender->id,
                'sender_phone' => $otpSender->number,
                'recipient_phone' => '09198765432',
                'sms_message_template_id' => $otpTemplate?->id,
                'sms_message_group_id' => null,
                'content' => 'کد تایید ورود شما: 102938\nاین کد به مدت ۲ دقیقه معتبر است.',
                'character_count' => 62,
                'page_count' => 1,
                'cost' => 0,
                'status' => 'failed',
                'error_reason' => 'شماره در بلک‌لیست مخابرات (عدم دریافت پیامک تبلیغاتی)',
                'tracking_id' => 'TRK-OTP-103',
                'created_at' => now()->subDays(1),
                'updated_at' => now()->subDays(1),
            ],
            // 4. Advertising Delivered
            [
                'ghorfe_online_list_id' => $business->id,
                'direction' => 'outbound',
                'service_type' => 'advertising',
                'sender_number_id' => $sender->id,
                'sender_phone' => $sender->number,
                'recipient_phone' => '09121113355',
                'sms_message_template_id' => $advTemplate?->id,
                'sms_message_group_id' => $salesGroup?->id,
                'content' => "جشنواره تخفیف ویژه شاهین!\nتا ۳۰٪ تخفیف روی تمامی خدمات با کد takhfif30\nورود: https://shahin.ir\nلغو 11",
                'character_count' => 105,
                'page_count' => 2,
                'cost' => 1200,
                'status' => 'delivered',
                'tracking_id' => 'TRK-ADV-201',
                'created_at' => now()->subHours(8),
                'updated_at' => now()->subHours(8),
            ],
            // 5. Inbound Reply (Opt-out 11)
            [
                'ghorfe_online_list_id' => $business->id,
                'direction' => 'inbound',
                'service_type' => 'advertising',
                'sender_number_id' => $sender->id,
                'sender_phone' => '09121113355',
                'recipient_phone' => $sender->number,
                'sms_message_template_id' => null,
                'sms_message_group_id' => null,
                'content' => '11',
                'character_count' => 2,
                'page_count' => 1,
                'cost' => 0,
                'status' => 'delivered',
                'tracking_id' => 'TRK-INB-301',
                'created_at' => now()->subHours(7),
                'updated_at' => now()->subHours(7),
            ],
            // 6. Normal Invoice Sent
            [
                'ghorfe_online_list_id' => $business->id,
                'direction' => 'outbound',
                'service_type' => 'normal',
                'sender_number_id' => $sender->id,
                'sender_phone' => $sender->number,
                'recipient_phone' => '09301112233',
                'sms_message_template_id' => $invoiceTemplate?->id,
                'sms_message_group_id' => $maliGroup?->id,
                'content' => 'مشتری گرامی، فاکتور شما به شماره INV-9021 صادر گردید.\nمبلغ: ۲,۵۰۰,۰۰۰ ریال\nجهت مشاهده و پرداخت به حساب کاربری مراجعه نمایید.',
                'character_count' => 118,
                'page_count' => 2,
                'cost' => 1000,
                'status' => 'sent',
                'tracking_id' => 'TRK-NORM-401',
                'created_at' => now()->subDays(2),
                'updated_at' => now()->subDays(2),
            ]
        ];

        foreach ($samples as $sample) {
            SmsMessage::firstOrCreate(
                ['tracking_id' => $sample['tracking_id']],
                $sample
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        SmsMessage::whereIn('tracking_id', [
            'TRK-OTP-101',
            'TRK-OTP-102',
            'TRK-OTP-103',
            'TRK-ADV-201',
            'TRK-INB-301',
            'TRK-NORM-401',
        ])->forceDelete();
    }
};
