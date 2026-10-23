<?php

/*
|--------------------------------------------------------------------------
| تحویل‌شده ولی اعمال‌نشده — تصمیم با CTO
|--------------------------------------------------------------------------
|
| This file is DELIVERED BUT NOT APPLIED. Everything below is commented out on purpose.
| It ports the stricter SMS two-step signing flow into a generic central backend:
|   - a purpose-bound code (the code is issued for one contract, one signer, one text hash),
|   - an attempt limit per code,
|   - a cooldown between codes,
|   - an hourly cap per signer,
|   - the text hash that the code is bound to.
|
| Live signing stays exactly as it is today: App\Http\Controllers\Admin\SmsContractSignController
| with App\Services\Sms\SmsContractOtpService. Nothing in the app calls this class.
|
| To enable: the CTO decides, then this block is uncommented, the namespace is checked against
| App\Services\Sms\SmsContractOtpService, and the class is wired into a controller and tested.
*/

// namespace App\Services\V1\Admin;
//
// use App\Exceptions\ContractMediaException;
// use App\Models\Contract;
// use App\Models\ContractSignature;
// use App\Models\GhorfeOnlineList;
// use Illuminate\Support\Facades\Cache;
// use Illuminate\Support\Facades\Hash;
// use Illuminate\Support\Str;
//
// class ContractSigningService
// {
//     /** Seconds a code stays valid. */
//     public const CODE_TTL_SECONDS = 300;
//
//     /** Wrong attempts allowed per code before it is burned. */
//     public const MAX_ATTEMPTS = 5;
//
//     /** Seconds between two codes for the same signer and purpose. */
//     public const COOLDOWN_SECONDS = 60;
//
//     /** Codes per signer per hour. */
//     public const HOURLY_CAP = 5;
//
//     /**
//      * Issues a code bound to (signer, purpose, contract, text hash). Returns the plain code
//      * to send by SMS to the signer's own phone. Only the hash is stored.
//      */
//     public function issue(int $signerUserId, string $purpose, ?Contract $contract, string $textHash): string
//     {
//         $cooldownKey = "contract_sign_cooldown:{$signerUserId}:{$purpose}";
//         if (Cache::has($cooldownKey)) {
//             throw new ContractMediaException('cooldown', 'کد قبلی هنوز معتبر است. کمی صبر کنید.');
//         }
//
//         $hourKey = "contract_sign_hour:{$signerUserId}:" . now()->format('YmdH');
//         $count = (int) Cache::get($hourKey, 0);
//         if ($count >= self::HOURLY_CAP) {
//             throw new ContractMediaException('hourly_limit', 'تعداد درخواست کد در این ساعت به حد مجاز رسیده است.');
//         }
//
//         $code = (string) random_int(100000, 999999);
//
//         Cache::put("contract_sign_code:{$signerUserId}:{$purpose}", [
//             'hash' => Hash::make($code),
//             'contract_id' => $contract?->id,
//             'text_hash' => $textHash,
//             'attempts' => 0,
//         ], now()->addSeconds(self::CODE_TTL_SECONDS));
//
//         Cache::put($cooldownKey, true, now()->addSeconds(self::COOLDOWN_SECONDS));
//         Cache::put($hourKey, $count + 1, now()->addHour());
//
//         return $code;
//     }
//
//     /**
//      * Checks a code against the stored purpose-bound record. A wrong code counts an attempt;
//      * after MAX_ATTEMPTS the record is removed. Success removes it too (one use only).
//      */
//     public function verify(int $signerUserId, string $purpose, string $code, ?Contract $contract, string $textHash): bool
//     {
//         $key = "contract_sign_code:{$signerUserId}:{$purpose}";
//         $record = Cache::get($key);
//
//         if (! $record || $record['contract_id'] !== $contract?->id || ! hash_equals((string) $record['text_hash'], $textHash)) {
//             return false;
//         }
//
//         if (! Hash::check($code, $record['hash'])) {
//             $record['attempts']++;
//             if ($record['attempts'] >= self::MAX_ATTEMPTS) {
//                 Cache::forget($key);
//             } else {
//                 Cache::put($key, $record, now()->addSeconds(self::CODE_TTL_SECONDS));
//             }
//
//             return false;
//         }
//
//         Cache::forget($key);
//
//         return true;
//     }
//
//     /** Records the signature once verify() passed. Same shape as the live dual write. */
//     public function record(GhorfeOnlineList $business, int $signerUserId, string $signatureName, string $signedPhone, string $textHash): ContractSignature
//     {
//         return ContractSignature::query()->create([
//             'location' => 'central',
//             'signable_type' => GhorfeOnlineList::class,
//             'signable_id' => $business->id,
//             'signer_user_id' => $signerUserId,
//             'signature_name' => Str::limit($signatureName, 191, ''),
//             'signed_phone' => $signedPhone,
//             'text_hash' => $textHash,
//             'otp_verified_at' => now(),
//             'signed_at' => now(),
//         ]);
//     }
// }
