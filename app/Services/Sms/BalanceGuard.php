<?php

namespace App\Services\Sms;

use App\Exceptions\Sms\InsufficientBalanceException;
use App\Models\GhorfeOnlineList;
use Illuminate\Support\Facades\DB;

class BalanceGuard
{
    /**
     * Get or initialize the OTP balance on the business.
     */
    public function getOtpBalance(GhorfeOnlineList $business): GhorfeOnlineList
    {
        $fresh = GhorfeOnlineList::find($business->id) ?? $business;
        if ($fresh->otp_balance === null) {
            $fresh->otp_balance = 0;
        }
        if ($fresh->otp_low_balance_threshold === null) {
            $fresh->otp_low_balance_threshold = 1000000;
        }
        return $fresh;
    }

    /**
     * Check OTP balance status and determine if sending is allowed,
     * whether low balance warning is triggered, or if a hard-stop is required.
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
    protected function evaluateOtp(GhorfeOnlineList $business, float $requiredCost = 0.0): array
    {
        $balance = (float) ($business->otp_balance ?? 0);
        $threshold = (float) ($business->otp_low_balance_threshold ?? 1000000);
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
    public function enforceOtp(GhorfeOnlineList $business, float $requiredCost = 0.0): GhorfeOnlineList
    {
        return DB::transaction(function () use ($business, $requiredCost) {
            $locked = GhorfeOnlineList::query()
                ->whereKey($business->getKey())
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
     * Get or initialize the advertising balance for a business.
     */
    public function getAdvertisingBalance(GhorfeOnlineList $business): GhorfeOnlineList
    {
        $fresh = GhorfeOnlineList::find($business->id) ?? $business;
        if ($fresh->advertising_balance === null) {
            $fresh->advertising_balance = 0;
        }
        return $fresh;
    }

    /**
     * Check whether the business' advertising balance covers the required cost.
     *
     * @return array{allowed: bool, balance: float, required: float, error: ?string}
     */
    public function checkAdvertising(GhorfeOnlineList $business, float $requiredCost): array
    {
        $advBalance = $this->getAdvertisingBalance($business);

        return $this->evaluateAdvertising((float) ($advBalance->advertising_balance ?? 0), $requiredCost);
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
    public function enforceAdvertising(GhorfeOnlineList $business, float $requiredCost): GhorfeOnlineList
    {
        return DB::transaction(function () use ($business, $requiredCost) {
            $locked = GhorfeOnlineList::query()
                ->whereKey($business->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $check = $this->evaluateAdvertising((float) ($locked->advertising_balance ?? 0), $requiredCost);

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
