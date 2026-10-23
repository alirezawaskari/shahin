<?php

namespace Tests\Unit;

use App\Services\SmsSettings\SmsSignatureRecord;
use PHPUnit\Framework\TestCase;

/**
 * The signing line's description is the only place the signer name, phone, OTP time and text
 * fingerprint are kept. These tests check that what is written reads back exactly.
 */
class SmsSignatureRecordTest extends TestCase
{
    public function test_description_round_trips_name_phone_time_and_hash(): void
    {
        $description = SmsSignatureRecord::describe('علی علوی', '09120000000', '2026-10-23 10:15:00', 'a1b2c3');

        $this->assertStringContainsString(SmsSignatureRecord::MARKER, $description);
        $this->assertSame(
            ['name' => 'علی علوی', 'phone' => '09120000000', 'verified_at' => '2026-10-23 10:15:00', 'hash' => 'a1b2c3'],
            SmsSignatureRecord::parse($description)
        );
    }

    public function test_empty_values_stay_empty(): void
    {
        $description = SmsSignatureRecord::describe('امضا', null, null, null);

        $this->assertSame(
            ['name' => 'امضا', 'phone' => '', 'verified_at' => null, 'hash' => null],
            SmsSignatureRecord::parse($description)
        );
    }

    public function test_migrated_line_carries_its_own_mark_and_still_parses(): void
    {
        $description = SmsSignatureRecord::describe('مدیر', '09121111111', '2025-01-02 03:04:05', 'h', true);

        $this->assertStringContainsString(SmsSignatureRecord::MIGRATED, $description);
        $this->assertSame('مدیر', SmsSignatureRecord::parse($description)['name']);
        $this->assertSame('2025-01-02 03:04:05', SmsSignatureRecord::parse($description)['verified_at']);
    }

    public function test_unrelated_text_is_not_a_signing(): void
    {
        $this->assertNull(SmsSignatureRecord::parse('تغییر وضعیت کسب‌وکار'));
        $this->assertNull(SmsSignatureRecord::parse(null));
    }
}
