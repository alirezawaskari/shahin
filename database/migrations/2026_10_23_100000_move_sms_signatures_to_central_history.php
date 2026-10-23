<?php

use App\Services\SmsSettings\SmsSignatureRecord;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Moves the legacy SMS contract signings into the central history, in the format SmsSignatureRecord
 * writes today: one histories row per business (reason 1, status 1, SMS marker, MIGRATED mark).
 *
 *  - No schema change. Source rows (ghorfe_settings sms_contract_* keys, and the real columns when
 *    they exist) are read only; nothing is deleted or altered.
 *  - Only a real signing counts: the sms_contract_accepted key (or column) must be truthy. The legacy
 *    `status == 1` fallback is never turned into a signature.
 *  - No signed text is invented: no contract_views row is written, because the text that was signed
 *    was never stored.
 *  - Values are copied as they are. An empty value stays empty.
 *  - Idempotent: a business that already has an SMS signing line is skipped.
 *  - Chunked over the source rows.
 *  - down() removes only the lines this migration wrote (the MIGRATED mark). Source data is untouched.
 */
return new class extends Migration
{
    /** The business morph type as stored in ghorfe_settings.settingable_type and histories.historiable_type. */
    private const BUSINESS_TYPE = 'App\\Models\\GhorfeOnlineList';

    public function up(): void
    {
        if (! Schema::hasTable('histories') || ! Schema::hasTable('ghorfe_settings') || ! Schema::hasTable('ghorfe_online_lists')) {
            return;
        }

        $hasRealColumns = Schema::hasColumn('ghorfe_online_lists', 'sms_contract_accepted');

        // Businesses with a real signing: the settings flag (the real column is checked per business below).
        DB::table('ghorfe_settings')
            ->where('settingable_type', self::BUSINESS_TYPE)
            ->where('key', 'sms_contract_accepted')
            ->orderBy('id')
            ->chunk(200, function ($rows) use ($hasRealColumns) {
                foreach ($rows as $row) {
                    if ($this->isTruthy($row->value)) {
                        $this->migrateBusiness((int) $row->settingable_id, $hasRealColumns);
                    }
                }
            });

        if ($hasRealColumns) {
            DB::table('ghorfe_online_lists')
                ->where('sms_contract_accepted', 1)
                ->orderBy('id')
                ->chunk(200, function ($rows) {
                    foreach ($rows as $row) {
                        $this->migrateBusiness((int) $row->id, true);
                    }
                });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('histories')) {
            return;
        }

        DB::table('histories')
            ->where('historiable_type', self::BUSINESS_TYPE)
            ->where('reason_id', SmsSignatureRecord::REASON_ID)
            ->where('status', SmsSignatureRecord::STATUS)
            ->where('description', 'like', '%' . SmsSignatureRecord::MIGRATED . '%')
            ->delete();
    }

    private function migrateBusiness(int $businessId, bool $hasRealColumns): void
    {
        // Idempotent: a business that already has an SMS signing line (migrated or made live) is skipped.
        $alreadyRecorded = DB::table('histories')
            ->where('historiable_type', self::BUSINESS_TYPE)
            ->where('historiable_id', $businessId)
            ->where('reason_id', SmsSignatureRecord::REASON_ID)
            ->where('status', SmsSignatureRecord::STATUS)
            ->where('description', 'like', '%' . SmsSignatureRecord::MARKER . '%')
            ->exists();

        if ($alreadyRecorded) {
            return;
        }

        $column = $hasRealColumns
            ? DB::table('ghorfe_online_lists')->where('id', $businessId)->first()
            : null;

        $read = function (string $key) use ($businessId, $column) {
            if ($column && property_exists($column, $key) && $column->{$key} !== null) {
                return (string) $column->{$key};
            }

            return DB::table('ghorfe_settings')
                ->where('settingable_type', self::BUSINESS_TYPE)
                ->where('settingable_id', $businessId)
                ->where('key', $key)
                ->value('value');
        };

        $name = (string) $read('sms_contract_signature_name');
        $phone = $read('sms_contract_signed_phone');
        $hash = $read('sms_contract_text_hash');
        $verifiedAt = $read('sms_contract_otp_verified_at') ?: $read('sms_contract_accepted_at');

        // The signer, when still a user; otherwise the line is written without one (both columns are nullable).
        $signerId = $read('sms_contract_accepted_by');
        if ($signerId !== null && $signerId !== '' && ! DB::table('users')->where('id', (int) $signerId)->exists()) {
            $signerId = null;
        }

        $createdAt = $verifiedAt ?: now()->format(SmsSignatureRecord::TIME_FORMAT);

        DB::table('histories')->insert([
            'historiable_id' => $businessId,
            'historiable_type' => self::BUSINESS_TYPE,
            'user_id' => $signerId !== null && $signerId !== '' ? (int) $signerId : null,
            'callcenter_id' => null,
            'status' => SmsSignatureRecord::STATUS,
            'reason_id' => SmsSignatureRecord::REASON_ID,
            'description' => SmsSignatureRecord::describe($name, $phone, $verifiedAt, $hash, true),
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ]);
    }

    private function isTruthy($value): bool
    {
        return in_array(strtolower(trim((string) $value)), ['1', 'true'], true);
    }
};
