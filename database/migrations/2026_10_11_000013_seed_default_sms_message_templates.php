<?php

use App\Models\SmsMessageGroup;
use App\Models\SmsMessageTemplate;
use App\Models\SmsTemplate;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $adminUser = User::where('phone', '09000000001')->first() 
            ?? User::where('level', 'superAdmin')->first() 
            ?? User::first();

        $adminId = $adminUser?->id;

        if (!$adminId) {
            return;
        }

        // 1. Sync legacy sms_templates to sms_message_templates
        if (Schema::hasTable('sms_templates')) {
            $legacyTemplates = SmsTemplate::all();
            foreach ($legacyTemplates as $legacy) {
                SmsMessageTemplate::firstOrCreate(
                    ['title' => $legacy->name ?: ($legacy->fa_name ?: 'قالب عمومی')],
                    [
                        'content' => $legacy->content,
                        'service_type' => 'normal',
                        'status' => $legacy->status ? 'active' : 'inactive',
                        'created_by' => $legacy->user_id ?: $adminId,
                    ]
                );
            }
        }

        // 2. Fetch sample groups if available
        $financialGroup = SmsMessageGroup::where('name', 'like', '%مالی%')->first();
        $supportGroup = SmsMessageGroup::where('name', 'like', '%پشتیبانی%')->first();
        $salesGroup = SmsMessageGroup::where('name', 'like', '%فروش%')->first();

        // 3. Seed production-ready templates
        $defaultTemplates = [
            // OTP Templates
            [
                'title' => 'کد تایید ورود (OTP)',
                'content' => "کد تایید شما: {کد}\nاین کد به مدت ۲ دقیقه معتبر است.",
                'sms_message_group_id' => null,
                'service_type' => 'otp',
                'status' => 'active',
                'created_by' => $adminId,
            ],
            [
                'title' => 'کد تایید تراکنش بانکی (OTP)',
                'content' => "کد تایید پرداخت شما: {کد}\nپلتفرم آنلاین شاهین",
                'sms_message_group_id' => $financialGroup?->id,
                'service_type' => 'otp',
                'status' => 'active',
                'created_by' => $adminId,
            ],

            // Normal / Transactional / Support Templates
            [
                'title' => 'صدور فاکتور جدید',
                'content' => "مشتری گرامی {نام}، فاکتور شما به شماره {شماره} صادر گردید.\nمبلغ: {مبلغ} ریال\nجهت مشاهده و پرداخت آنلاین به حساب خود مراجعه نمایید.",
                'sms_message_group_id' => $financialGroup?->id,
                'service_type' => 'normal',
                'status' => 'active',
                'created_by' => $adminId,
            ],
            [
                'title' => 'یادآوری نوبت و جلسه',
                'content' => "مراجعه‌کننده گرامی {نام}، نوبت شما برای تاریخ {تاریخ} و ساعت {ساعت} ثبت گردید.\nلطفاً ۱۰ دقیقه قبل در محل حضور داشته باشید.",
                'sms_message_group_id' => $supportGroup?->id,
                'service_type' => 'normal',
                'status' => 'active',
                'created_by' => $adminId,
            ],
            [
                'title' => 'اطلاع‌رسانی وضعیت سفارش',
                'content' => "سفارش شما به شناسه {شناسه} در وضعیت {وضعیت} قرار گرفت.\nبا تشکر از اعتماد و همراهی شما.",
                'sms_message_group_id' => $supportGroup?->id,
                'service_type' => 'normal',
                'status' => 'active',
                'created_by' => $adminId,
            ],

            // Advertising Templates
            [
                'title' => 'جشنواره تخفیف ویژه مشتریان',
                'content' => "جشنواره تخفیف ویژه شاهین!\nتا ۳۰٪ تخفیف روی تمامی خدمات با کد takhfif30\nورود به سامانه: https://shahin.ir",
                'sms_message_group_id' => $salesGroup?->id,
                'service_type' => 'advertising',
                'status' => 'active',
                'created_by' => $adminId,
            ],
            [
                'title' => 'معرفی خدمات جدید سامانه',
                'content' => "همراه گرامی، خدمات جدید و هوشمند پیامکی سامانه فعال شد.\nجهت بررسی و فعال‌سازی رایگان به حساب کاربری خود مراجعه کنید.",
                'sms_message_group_id' => $salesGroup?->id,
                'service_type' => 'advertising',
                'status' => 'active',
                'created_by' => $adminId,
            ],
        ];

        foreach ($defaultTemplates as $templateData) {
            SmsMessageTemplate::firstOrCreate(
                ['title' => $templateData['title']],
                $templateData
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        SmsMessageTemplate::whereIn('title', [
            'کد تایید ورود (OTP)',
            'کد تایید تراکنش بانکی (OTP)',
            'صدور فاکتور جدید',
            'یادآوری نوبت و جلسه',
            'اطلاع‌رسانی وضعیت سفارش',
            'جشنواره تخفیف ویژه مشتریان',
            'معرفی خدمات جدید سامانه',
        ])->forceDelete();
    }
};
