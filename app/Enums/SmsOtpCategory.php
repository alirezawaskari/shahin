<?php

namespace App\Enums;

/**
 * The four FIXED system categories every OTP message/template carries. Deliberately independent
 * of SmsMessageGroup (the editable, per-business message grouping and its confidentiality rules).
 * Promotional (advertising/normal) messages never carry one.
 */
enum SmsOtpCategory: string
{
    case Auth = 'auth_password_reset';
    case Sales = 'sales';
    case Finance = 'finance';
    case Legal = 'legal';

    public function label(): string
    {
        return match ($this) {
            self::Auth => 'احراز هویت / بازیابی رمز عبور',
            self::Sales => 'فروش',
            self::Finance => 'مالی',
            self::Legal => 'حقوقی',
        };
    }

    /** @return array<string, string> slug => Persian label */
    public static function options(): array
    {
        $out = [];
        foreach (self::cases() as $case) {
            $out[$case->value] = $case->label();
        }

        return $out;
    }

    /** @return array<int, string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function labelFor(?string $slug): ?string
    {
        $case = $slug ? self::tryFrom($slug) : null;

        return $case?->label();
    }
}
