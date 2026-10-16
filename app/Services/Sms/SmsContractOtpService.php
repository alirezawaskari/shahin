<?php

namespace App\Services\Sms;

use App\Contracts\Auth\OtpGeneratorInterface;
use App\Contracts\Auth\OtpNotifierInterface;
use App\Exceptions\Auth\OtpNotificationException;
use App\Exceptions\Sms\ContractOtpException;
use App\Models\GhorfeOnlineList;
use App\Models\SmsContractOtp;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * 6-digit OTP that verifies the person signing the SMS usage contract + telecom rules.
 *
 * Unlike the admin-login OtpService this one is purpose-bound: the code only works for the
 * signer it was sent to, for the business being signed, for the exact contract/rules text that
 * was shown, and for the signature name that was typed — so a login code (or a code for other
 * wording) can never be replayed as a signature. It goes ONLY to the signer's own account phone.
 *
 * It is deliberately independent of the business' SMS credit: a business must sign before it can
 * even buy credit.
 */
class SmsContractOtpService
{
    public function __construct(
        private readonly OtpGeneratorInterface $generator,
        private readonly OtpNotifierInterface $notifier,
        private readonly BulkSmsRecipientFilterService $phones,
        private readonly int $ttlSeconds = 300,
        private readonly int $maxAttempts = 5,
        private readonly int $resendCooldownSeconds = 60,
        private readonly int $maxSendsPerHour = 5,
    ) {
    }

    /**
     * Issue a code to the signer's own phone.
     *
     * @return array{expires_in: int, resend_after: int, masked_phone: string}
     * @throws ContractOtpException
     */
    public function send(User $signer, GhorfeOnlineList $business, string $contractText, string $rulesText, string $signatureName): array
    {
        $phone = $this->phones->normalizePhoneNumber((string) $signer->phone);
        if ($phone === null) {
            throw new ContractOtpException('missing_phone', 'شماره موبایل معتبری برای حساب کاربری شما ثبت نشده است. ابتدا شماره موبایل خود را در پروفایل تکمیل کنید.');
        }

        $latest = SmsContractOtp::where('user_id', $signer->id)->latest('id')->first();
        if ($latest && $latest->created_at->diffInSeconds(now(), true) < $this->resendCooldownSeconds) {
            $wait = (int) ceil($this->resendCooldownSeconds - $latest->created_at->diffInSeconds(now(), true));
            throw new ContractOtpException('cooldown', "برای دریافت کد جدید {$wait} ثانیه صبر کنید.", $wait);
        }

        $sentLastHour = SmsContractOtp::where('user_id', $signer->id)->where('created_at', '>=', now()->subHour())->count();
        if ($sentLastHour >= $this->maxSendsPerHour) {
            throw new ContractOtpException('hourly_limit', 'تعداد درخواست کد در یک ساعت گذشته به سقف مجاز رسیده است. بعدا دوباره تلاش کنید.', 3600);
        }

        $code = $this->generator->generate();
        $contractHash = $this->contractHash($contractText, $rulesText);

        $otp = DB::transaction(function () use ($signer, $business, $phone, $code, $contractHash, $signatureName) {
            // Only the newest code is ever valid.
            SmsContractOtp::where('user_id', $signer->id)
                ->where('ghorfe_online_list_id', $business->id)
                ->whereNull('used_at')
                ->update(['used_at' => now()]);

            return SmsContractOtp::create([
                'ghorfe_online_list_id' => $business->id,
                'user_id' => $signer->id,
                'phone' => $phone,
                'code_hash' => $this->codeHash($signer->id, $business->id, $contractHash, $signatureName, $code),
                'contract_hash' => $contractHash,
                'signature_name_hash' => $this->nameHash($signatureName),
                'expires_at' => now()->addSeconds($this->ttlSeconds),
            ]);
        });

        try {
            $this->notifier->send($phone, $code);
        } catch (OtpNotificationException $e) {
            // Nothing was delivered: do not make the signer wait out a cooldown for a code they never got.
            $otp->delete();
            throw new ContractOtpException('send_failed', 'ارسال پیامک کد تایید ناموفق بود. چند لحظه بعد دوباره تلاش کنید.');
        }

        return [
            'expires_in' => $this->ttlSeconds,
            'resend_after' => $this->resendCooldownSeconds,
            'masked_phone' => $this->maskPhone($phone),
        ];
    }

    /**
     * Check the code a signer typed. On success the code is consumed (single use) and the
     * verified record is returned as the evidence of the signature.
     *
     * @throws ContractOtpException
     */
    public function verify(User $signer, GhorfeOnlineList $business, string $contractText, string $rulesText, string $signatureName, string $code): SmsContractOtp
    {
        // The wrong-attempt counter must be COMMITTED even though we then throw, so the outcome is
        // computed inside the transaction and the exception is raised after it has committed.
        $outcome = DB::transaction(function () use ($signer, $business, $contractText, $rulesText, $signatureName, $code) {
            $otp = SmsContractOtp::where('user_id', $signer->id)
                ->where('ghorfe_online_list_id', $business->id)
                ->whereNull('used_at')
                ->latest('id')
                ->lockForUpdate() // two simultaneous submissions must not both consume one code
                ->first();

            if (!$otp) {
                return ['error' => ['not_requested', 'ابتدا کد تایید را دریافت کنید.']];
            }
            if ($otp->expires_at->isPast()) {
                return ['error' => ['expired', 'کد تایید منقضی شده است. کد جدید دریافت کنید.']];
            }
            if ($otp->attempts >= $this->maxAttempts) {
                $otp->update(['used_at' => now()]);
                return ['error' => ['locked', 'تعداد تلاش‌های ناموفق به سقف رسید. کد جدید دریافت کنید.']];
            }

            $contractHash = $this->contractHash($contractText, $rulesText);
            if (!hash_equals($otp->contract_hash, $contractHash)) {
                return ['error' => ['contract_changed', 'متن تعهدنامه یا ضوابط پس از ارسال کد تغییر کرده است. کد جدید دریافت کنید.']];
            }
            if (!hash_equals($otp->signature_name_hash, $this->nameHash($signatureName))) {
                return ['error' => ['name_changed', 'نام امضاکننده پس از ارسال کد تغییر کرده است. کد جدید دریافت کنید.']];
            }

            $expected = $this->codeHash($signer->id, $business->id, $contractHash, $signatureName, trim($code));
            if (!hash_equals($otp->code_hash, $expected)) {
                $attempts = $otp->attempts + 1;
                $otp->update(['attempts' => $attempts, 'used_at' => $attempts >= $this->maxAttempts ? now() : null]);
                $left = $this->maxAttempts - $attempts;

                return ['error' => $left > 0
                    ? ['invalid', "کد تایید نادرست است. {$left} تلاش باقی مانده است."]
                    : ['locked', 'تعداد تلاش‌های ناموفق به سقف رسید. کد جدید دریافت کنید.']];
            }

            $otp->update(['used_at' => now()]);

            return ['otp' => $otp];
        });

        if (isset($outcome['error'])) {
            throw new ContractOtpException($outcome['error'][0], $outcome['error'][1]);
        }

        return $outcome['otp'];
    }

    /** sha256 of exactly what the signer was shown. */
    public function contractHash(string $contractText, string $rulesText): string
    {
        return hash('sha256', $contractText . "\n--- rules ---\n" . $rulesText);
    }

    public function maskPhone(string $phone): string
    {
        return substr($phone, 0, 4) . '***' . substr($phone, -4);
    }

    private function nameHash(string $name): string
    {
        return hash('sha256', trim(preg_replace('/\s+/u', ' ', $name)));
    }

    /** HMAC bound to signer + business + text + name + code; the plain code is never stored. */
    private function codeHash(int $userId, int $businessId, string $contractHash, string $signatureName, string $code): string
    {
        return hash_hmac(
            'sha256',
            implode('|', [$userId, $businessId, $contractHash, $this->nameHash($signatureName), $code]),
            (string) config('app.key')
        );
    }
}
