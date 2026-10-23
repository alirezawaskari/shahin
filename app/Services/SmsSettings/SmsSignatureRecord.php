<?php

namespace App\Services\SmsSettings;

use App\Models\ContractView;
use App\Models\GhorfeOnlineList;
use App\Models\History;

/**
 * The SMS contract signature, kept where the central module keeps its signings: one row in the
 * `histories` table on the business (reason «تایید قرارداد دریافت خدمت», status 1), plus the exact
 * signed text in `contract_views`. The signer name, phone, OTP time and text fingerprint live in
 * the line's description, in the format below, and are read back by parse().
 */
final class SmsSignatureRecord
{
    /** reasons.id = 1: «تایید قرارداد دریافت خدمت», the reason the central signing lines use. */
    public const REASON_ID = 1;

    public const STATUS = 1;

    /** Marks a line as an SMS signing, so it is never confused with other reason-1 lines. */
    public const MARKER = '[امضای تعهدنامه پیامک]';

    /** Marks a line that the data migration copied from legacy sms_contract_* settings. */
    public const MIGRATED = '[منتقل' . "\u{200C}" . 'شده]';

    /** Timestamp format used inside the description (Gregorian, so it parses back exactly). */
    public const TIME_FORMAT = 'Y-m-d H:i:s';

    private const PATTERN = '/امضاکننده: «(.*)» \| شماره: (.*) \| تایید کد: (.*) \| هش متن: (.*)$/us';

    public static function describe(string $name, ?string $phone, ?string $verifiedAt, ?string $hash, bool $migrated = false): string
    {
        return self::MARKER
            . ($migrated ? self::MIGRATED : '')
            . ' امضاکننده: «' . $name . '»'
            . ' | شماره: ' . (string) $phone
            . ' | تایید کد: ' . (string) $verifiedAt
            . ' | هش متن: ' . (string) $hash;
    }

    /**
     * @return array{name: string, phone: string, verified_at: ?string, hash: ?string}|null
     */
    public static function parse(?string $description): ?array
    {
        if ($description === null || ! preg_match(self::PATTERN, $description, $m)) {
            return null;
        }

        return [
            'name' => $m[1],
            'phone' => $m[2],
            'verified_at' => $m[3] !== '' ? $m[3] : null,
            'hash' => $m[4] !== '' ? $m[4] : null,
        ];
    }

    /**
     * The signing line of a business, or null. The legacy `status == 1` fallback is NOT a signing line.
     */
    public static function lineFor(GhorfeOnlineList $business): ?History
    {
        return $business->histories()
            ->where('reason_id', self::REASON_ID)
            ->where('status', self::STATUS)
            ->where('description', 'like', '%' . self::MARKER . '%')
            ->latest('id')
            ->first();
    }

    /**
     * Writes the signing: the history line (signer = the logged-in user) and the snapshot of the
     * exact signed text, so later edits to the templates cannot change what was signed.
     */
    public static function record(GhorfeOnlineList $business, int $signerId, string $name, ?string $phone, string $verifiedAt, ?string $hash, string $signedText): void
    {
        History::contractHistoryCreate(
            $business,
            null,
            $signerId,
            self::REASON_ID,
            self::STATUS,
            self::describe($name, $phone, $verifiedAt, $hash)
        );

        ContractView::store($business, $signedText);
    }
}
