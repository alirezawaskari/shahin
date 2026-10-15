<?php

namespace App\Services\Sms;

use App\Exceptions\Sms\InsufficientBalanceException;
use App\Models\AdvertisingBalance;
use App\Models\GhorfeOnlineList;
use App\Models\OtpBalance;
use Illuminate\Support\Facades\DB;

class BalanceGuard
{
    /**
     * Get or create the OTP balance record for a business.
     */
    public function getOtpBalance(GhorfeOnlineList $business): OtpBalance
    {
        return OtpBalance::firstOrCreate(
            ['ghorfe_online_list_id' => $business->id],
            [
                'low_balance_threshold' => 1000000, // 100,000 Tomans (1,000,000 Rials)
            ]
        );
    }

    /**
     * Check OTP balance status and determine if sending is allowed,
     * whether low balance warning is triggered, or if a hard-stop is required.
     *
     * Rules:
     * - Hard-stop: when balance <= 0 OR balance < requiredCost.
     * - Warning: when balance <= low_balance_threshold (even if sending is still permitted).
     *
     * @return array{
     *     allowed: bool,
     *     balance: float,
     *     low_balance_threshold: float,
     *     is_low_balance: bool,
     *     warning: ?string,
     *     error: ?string
     * }
     */
    public function checkOtp(GhorfeOnlineList $business, float $requiredCost = 0.0): array
    {
        return $this->evaluateOtp($this->getOtpBalance($business), $requiredCost);
    }

    /**
     * Evaluate an already-loaded OTP balance row against the required cost.
     */
    protected function evaluateOtp(OtpBalance $otpBalance, float $requiredCost = 0.0): array
    {
        $balance = (float) $otpBalance->balance;
        $threshold = (float) $otpBalance->low_balance_threshold;
        $isLowBalance = $balance <= $threshold;

        // Hard-stop check: zero balance or less than required cost
        if ($balance <= 0) {
            return [
                'allowed' => false,
                'balance' => $balance,
                'low_balance_threshold' => $threshold,
                'is_low_balance' => true,
                'warning' => null,
                'error' => 'موجودی پیامک اعتبارسنجی (OTP) به اتمام رسیده است (صفر ریال). ارسال پیامک متوقف شده است.',
            ];
        }

        if ($requiredCost > 0 && $balance < $requiredCost) {
            return [
                'allowed' => false,
                'balance' => $balance,
                'low_balance_threshold' => $threshold,
                'is_low_balance' => true,
                'warning' => null,
                'error' => sprintf(
                    'موجودی پیامک اعتبارسنجی (OTP) ناکافی است. موجودی فعلی: %s ریال، هزینه مورد نیاز: %s ریال.',
                    number_format($balance),
                    number_format($requiredCost)
                ),
            ];
        }

        // Allowed to send, but may have a low-balance warning
        $warning = null;
        if ($isLowBalance) {
            $warning = sprintf(
                'هشدار: موجودی پیامک اعتبارسنجی (OTP) رو به اتمام است (%s ریال باقی مانده، آستانه هشدار: %s ریال). لطفا نسبت به شارژ حساب اقدام نمایید.',
                number_format($balance),
                number_format($threshold)
            );
        }

        return [
            'allowed' => true,
            'balance' => $balance,
            'low_balance_threshold' => $threshold,
            'is_low_balance' => $isLowBalance,
            'warning' => $warning,
            'error' => null,
        ];
    }

    /**
     * Enforce OTP balance and throw InsufficientBalanceException if not allowed to send.
     *
     * @throws InsufficientBalanceException
     */
    public function enforceOtp(GhorfeOnlineList $business, float $requiredCost = 0.0): OtpBalance
    {
        // The check runs against a locked row. The lock is held until the enclosing
        // transaction ends, so callers that debit must do so within this same transaction.
        return DB::transaction(function () use ($business, $requiredCost) {
            $otpBalance = $this->getOtpBalance($business);
            $locked = OtpBalance::query()
                ->whereKey($otpBalance->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $check = $this->evaluateOtp($locked, $requiredCost);

            if (!$check['allowed']) {
                throw new InsufficientBalanceException(
                    $check['error'] ?? 'موجودی پیامک اعتبارسنجی (OTP) برای ارسال کافی نیست.',
                    $check['balance'],
                    $requiredCost
                );
            }

            return $locked;
        });
    }

    /**
     * Get or create the advertising balance for a business.
     * Credit belongs to the advertising panel, so every advertising line of the
     * business spends from this one balance.
     */
    public function getAdvertisingBalance(GhorfeOnlineList $business): AdvertisingBalance
    {
        return AdvertisingBalance::firstOrCreate(
            ['ghorfe_online_list_id' => $business->id],
            []
        );
    }

    /**
     * Check whether the business' advertising balance covers the required cost.
     *
     * @return array{allowed: bool, balance: float, required: float, error: ?string}
     */
    public function checkAdvertising(GhorfeOnlineList $business, float $requiredCost): array
    {
        $advBalance = $this->getAdvertisingBalance($business);

        return $this->evaluateAdvertising((float) $advBalance->balance, $requiredCost);
    }

    /**
     * Evaluate a known advertising balance figure against the required cost.
     *
     * @return array{allowed: bool, balance: float, required: float, error: ?string}
     */
    protected function evaluateAdvertising(float $balance, float $requiredCost): array
    {
        if ($balance >= $requiredCost) {
            return [
                'allowed' => true,
                'balance' => $balance,
                'required' => $requiredCost,
                'error' => null,
            ];
        }

        return [
            'allowed' => false,
            'balance' => $balance,
            'required' => $requiredCost,
            'error' => sprintf(
                'موجودی پنل پیامک تبلیغاتی ناکافی است (موجودی: %s ریال، هزینه: %s ریال).',
                number_format($balance),
                number_format($requiredCost)
            ),
        ];
    }

    /**
     * Enforce advertising balance and throw InsufficientBalanceException if insufficient.
     *
     * @throws InsufficientBalanceException
     */
    public function enforceAdvertising(GhorfeOnlineList $business, float $requiredCost): AdvertisingBalance
    {
        // Same locking contract as enforceOtp(): call from within the debit transaction.
        return DB::transaction(function () use ($business, $requiredCost) {
            $advBalance = $this->getAdvertisingBalance($business);
            $locked = AdvertisingBalance::query()
                ->whereKey($advBalance->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $check = $this->evaluateAdvertising((float) $locked->balance, $requiredCost);

            if (!$check['allowed']) {
                throw new InsufficientBalanceException(
                    $check['error'] ?? 'موجودی پنل پیامک تبلیغاتی برای ارسال کافی نیست.',
                    $check['balance'],
                    $requiredCost
                );
            }

            return $locked;
        });
    }
}
