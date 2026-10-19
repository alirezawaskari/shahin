<?php

namespace App\Support;

/**
 * Single source of Persian display labels for SMS service types, statuses and directions,
 * so raw English values (otp, delivered, ...) never leak into the UI.
 */
class SmsLabels
{
    public const SERVICE_TYPES = [
        'otp' => 'اعتبارسنجی (OTP)',
        'advertising' => 'تبلیغاتی',
        'normal' => 'عادی',
    ];

    public const STATUSES = [
        'queued' => 'در صف ارسال',
        'pending' => 'در انتظار',
        'processing' => 'در حال پردازش',
        'sent' => 'ارسال شد',
        'delivered' => 'تحویل شد',
        'failed' => 'ناموفق',
        'rejected' => 'رد شده',
        'expired' => 'منقضی شده',
        'undelivered' => 'تحویل نشده',
        'cancelled' => 'لغو شده',
        'canceled' => 'لغو شده',
        'completed' => 'تکمیل شد',
        'pending_queue' => 'در صف تأیید',
        'scheduled' => 'زمان‌بندی شده',
        'running' => 'در حال اجرا',
        'draft' => 'پیش‌نویس',
    ];

    public const DIRECTIONS = [
        'inbound' => 'دریافتی',
        'outbound' => 'ارسالی',
    ];

    public static function serviceType(?string $value): string
    {
        return self::SERVICE_TYPES[$value ?? ''] ?? ($value ? self::SERVICE_TYPES['normal'] : '-');
    }

    public static function status(?string $value): string
    {
        if ($value === null || $value === '') {
            return '-';
        }

        return self::STATUSES[strtolower($value)] ?? $value;
    }

    public static function direction(?string $value): string
    {
        return self::DIRECTIONS[$value ?? ''] ?? self::DIRECTIONS['outbound'];
    }
}
