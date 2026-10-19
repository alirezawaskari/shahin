<?php

namespace App\Services\Sms;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * The ONE central list of inbound SMS texts that already mean something to the system (or to the
 * telecom industry) and therefore can never be used as an auto-responder trigger, and are never
 * auto-answered. Every entry is traced to the place in the code that interprets it.
 *
 * Static entries (compared after SmsKeywordNormalizer::normalize):
 *   '11', 'لغو 11', 'لغو11'  opt-out. Appended to every advertising SMS (SingleSmsSendService::CANCEL_PHRASE);
 *                            an inbound body of 11 creates a Callcenter row with status 11
 *                            («انصراف از دریافت پیامک», Callcenter::status()) in Console\Commands\Spreadsms.
 *   '12'                     re-subscribe: the (commented-out) opt-out confirmation in Spreadsms::answerCancelReceivingSms
 *                            tells the user to send 12 to receive messages again.
 *   stop, stopall, unsubscribe, cancel, end, quit, help, info, start, unstop
 *                            CTIA / GSMA industry-standard keywords gateways and carriers act on by themselves.
 *   لغو, توقف, راهنما       Persian equivalents of CANCEL / STOP / HELP.
 *
 * Dynamic entries (read from the database at call time, because the system maps them from inbound text):
 *   service ids             Spreadsms: Service::find($sms->body) attaches the service to the new request.
 *   sms_code values         Spreadsms: sms body IN users/representations sms_code routes the SMS to that
 *                           marketer / representation; ghorfe_online_lists.sms_code and organizations.sms_code
 *                           are the same kind of code assigned to a business / organization.
 *
 * The contract-signing OTP (SmsContractOtpService) is a random 6-digit code sent OUT to the signer; it is never
 * interpreted from inbound SMS, so it has no entry here (the engine also never answers inbound OTP traffic).
 */
class SmsReservedCodes
{
    public const TYPE_OPT_OUT = 'opt_out';
    public const TYPE_OPT_IN = 'opt_in';
    public const TYPE_INDUSTRY = 'industry';
    public const TYPE_SERVICE_ID = 'service_id';
    public const TYPE_SMS_CODE = 'sms_code';

    /**
     * @return array<string, array{type: string, source: string}> keyed by normalized text
     */
    public static function staticEntries(): array
    {
        $optOut = 'لغو دریافت پیامک (SingleSmsSendService::CANCEL_PHRASE، Spreadsms وضعیت ۱۱)';
        $industry = 'کلمه استاندارد اپراتورها (CTIA/GSMA)';

        $entries = [
            '11' => ['type' => self::TYPE_OPT_OUT, 'source' => $optOut],
            'لغو 11' => ['type' => self::TYPE_OPT_OUT, 'source' => $optOut],
            'لغو11' => ['type' => self::TYPE_OPT_OUT, 'source' => $optOut],
            '12' => ['type' => self::TYPE_OPT_IN, 'source' => 'فعال‌سازی مجدد دریافت پیامک (Spreadsms::answerCancelReceivingSms)'],
            'لغو' => ['type' => self::TYPE_OPT_OUT, 'source' => 'معادل فارسی CANCEL (لغو دریافت پیامک)'],
            'توقف' => ['type' => self::TYPE_OPT_OUT, 'source' => 'معادل فارسی STOP'],
            'راهنما' => ['type' => self::TYPE_INDUSTRY, 'source' => 'معادل فارسی HELP'],
        ];

        foreach (['stop', 'stopall', 'unsubscribe', 'cancel', 'end', 'quit', 'help', 'info', 'start', 'unstop'] as $word) {
            $entries[$word] = ['type' => self::TYPE_INDUSTRY, 'source' => $industry];
        }

        return $entries;
    }

    /**
     * Static + database-backed entries, keyed by normalized text.
     *
     * @return array<string, array{type: string, source: string}>
     */
    public static function all(): array
    {
        return self::staticEntries() + self::dynamicEntries();
    }

    /**
     * @return array<string, array{type: string, source: string}>
     */
    public static function dynamicEntries(): array
    {
        $entries = [];

        try {
            if (Schema::hasTable('services')) {
                $query = DB::table('services');
                if (Schema::hasColumn('services', 'deleted_at')) {
                    $query->whereNull('deleted_at');
                }
                foreach ($query->pluck('id') as $id) {
                    $entries[(string) $id] ??= [
                        'type' => self::TYPE_SERVICE_ID,
                        'source' => 'شناسه خدمت (Spreadsms: Service::find)',
                    ];
                }
            }

            $owners = [
                'users' => 'کد پیامکی کاربر/بازاریاب',
                'representations' => 'کد پیامکی نمایندگی',
                'ghorfe_online_lists' => 'کد پیامکی کسب‌وکار',
                'organizations' => 'کد پیامکی سازمان',
            ];
            foreach ($owners as $table => $label) {
                if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'sms_code')) {
                    continue;
                }
                foreach (DB::table($table)->whereNotNull('sms_code')->pluck('sms_code') as $raw) {
                    foreach (self::decodeCodes($raw) as $code) {
                        $key = SmsKeywordNormalizer::normalize($code);
                        if ($key !== '') {
                            $entries[$key] ??= ['type' => self::TYPE_SMS_CODE, 'source' => $label];
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            // Never block the webhook or the settings page because the lookup tables are unavailable.
            Log::warning('SmsReservedCodes dynamic lookup failed: ' . $e->getMessage());
        }

        return $entries;
    }

    /**
     * The entry that $text (any raw text) collides with, or null. Also matches with whitespace removed.
     *
     * @return array{code: string, type: string, source: string}|null
     */
    public static function find(string $text, ?array $entries = null): ?array
    {
        $entries ??= self::all();
        $normalized = SmsKeywordNormalizer::normalize($text);
        if ($normalized === '') {
            return null;
        }

        foreach ([$normalized, SmsKeywordNormalizer::compact($text)] as $candidate) {
            if (isset($entries[$candidate])) {
                return ['code' => $candidate] + $entries[$candidate];
            }
        }

        return null;
    }

    public static function isReserved(string $text, ?array $entries = null): bool
    {
        return self::find($text, $entries) !== null;
    }

    /**
     * First reserved entry that starts with the given prefix (so a starts_with trigger would shadow it).
     *
     * @return array{code: string, type: string, source: string}|null
     */
    public static function shadowedBy(string $prefix, ?array $entries = null): ?array
    {
        $entries ??= self::all();
        $prefix = SmsKeywordNormalizer::normalize($prefix);
        if ($prefix === '') {
            return null;
        }

        foreach ($entries as $code => $meta) {
            if (str_starts_with((string) $code, $prefix)) {
                return ['code' => (string) $code] + $meta;
            }
        }

        return null;
    }

    /**
     * True when an inbound text must never be auto-answered: the whole text is reserved, or its first word is a
     * static opt-out / industry word ("STOP please", "لغو 11 ممنون").
     */
    public static function isProtectedInbound(string $text, ?array $entries = null): bool
    {
        $entries ??= self::all();
        if (self::isReserved($text, $entries)) {
            return true;
        }

        $first = explode(' ', SmsKeywordNormalizer::normalize($text))[0] ?? '';

        return $first !== '' && isset(self::staticEntries()[$first]);
    }

    /**
     * @return string[]
     */
    protected static function decodeCodes(mixed $raw): array
    {
        if (is_array($raw)) {
            return array_map('strval', $raw);
        }
        $raw = trim((string) $raw);
        if ($raw === '') {
            return [];
        }
        $decoded = json_decode($raw, true);
        if (is_array($decoded)) {
            return array_map('strval', array_filter($decoded, fn ($v) => is_scalar($v)));
        }

        return array_map('trim', explode(',', $raw));
    }
}
