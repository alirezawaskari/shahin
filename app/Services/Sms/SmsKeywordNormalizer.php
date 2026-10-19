<?php

namespace App\Services\Sms;

/**
 * One canonical form for SMS keywords, used by both the rule validator and the inbound engine so
 * "Stop", " stop ", "ﺳﺘﺎپ" typed with Arabic letters or "۱۱" / "١١" all compare equal to their Latin/Persian form.
 */
class SmsKeywordNormalizer
{
    public static function normalize(?string $text): string
    {
        $text = (string) $text;

        // Persian (۰-۹) and Arabic-Indic (٠-٩) digits -> Latin digits.
        $text = strtr($text, [
            '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
            '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
            '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
        ]);

        // Arabic letter variants typed on Arabic keyboards -> Persian letters.
        $text = strtr($text, ['ي' => 'ی', 'ى' => 'ی', 'ك' => 'ک', 'ة' => 'ه']);

        // Invisible formatting marks and tatweel carry no meaning; ZWNJ acts like a space.
        $text = str_replace(["\u{200C}"], ' ', $text);
        $text = preg_replace('/[\x{200B}\x{200D}\x{200E}\x{200F}\x{FEFF}\x{0640}]/u', '', $text) ?? $text;

        $text = mb_strtolower($text, 'UTF-8');

        // Collapse every run of whitespace to one space, then trim.
        $text = preg_replace('/\s+/u', ' ', $text) ?? $text;

        return trim($text);
    }

    /**
     * Same as normalize() but with all whitespace removed ("لغو11" == "لغو 11").
     */
    public static function compact(?string $text): string
    {
        return str_replace(' ', '', self::normalize($text));
    }
}
