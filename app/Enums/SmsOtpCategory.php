<?php

namespace App\Enums;

use App\Models\Setting;

/**
 * System categories for OTP messages/templates.
 * Connected directly to the groups/folders defined in Settings (sms_message_group).
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

        try {
            $dbGroups = Setting::where('key', 'sms_message_group')->get();
            foreach ($dbGroups as $dbg) {
                if ($dbg->slug) {
                    $out[$dbg->slug] = $dbg->name ?: ($out[$dbg->slug] ?? $dbg->slug);
                }
            }
        } catch (\Throwable $e) {
            // fallback
        }

        return $out;
    }

    /** @return array<int, string> */
    public static function values(): array
    {
        return array_keys(self::options());
    }

    public static function labelFor(?string $slug): ?string
    {
        if (!$slug) {
            return null;
        }

        $options = self::options();
        if (isset($options[$slug])) {
            return $options[$slug];
        }

        $case = self::tryFrom($slug);

        return $case ? $case->label() : $slug;
    }
}
