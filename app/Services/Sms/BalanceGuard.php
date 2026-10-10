<?php

namespace App\Services\Sms;

use App\Exceptions\Sms\InsufficientBalanceException;
use App\Models\AdvertisingBalance;
use App\Models\GhorfeOnlineList;
use App\Models\OtpBalance;
use App\Models\SenderNumber;
use Illuminate\Support\Collection;
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
                'low_balance_threshold' => 100000,
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
     * Get or create the advertising balance for a specific business and sender number.
     */
    public function getAdvertisingBalance(GhorfeOnlineList $business, SenderNumber $senderNumber): AdvertisingBalance
    {
        return AdvertisingBalance::firstOrCreate(
            [
                'ghorfe_online_list_id' => $business->id,
                'sender_number_id' => $senderNumber->id,
            ],
            []
        );
    }

    /**
     * Find alternative active advertising sender numbers assigned to this business
     * that possess sufficient balance (>= requiredCost).
     *
     * @return Collection<int, SenderNumber>
     */
    public function findAlternativeAdvertisingSenders(
        GhorfeOnlineList $business,
        float $requiredCost,
        ?int $excludeSenderNumberId = null
    ): Collection {
        $senders = $business->senderNumbers()
            ->where('sender_numbers.type', 'advertising')
            ->where('sender_numbers.status', 'active')
            ->when($excludeSenderNumberId, fn ($q) => $q->where('sender_numbers.id', '!=', $excludeSenderNumberId))
            ->get();

        $validAlternatives = collect();

        foreach ($senders as $sender) {
            $balance = (float) (AdvertisingBalance::where('ghorfe_online_list_id', $business->id)
                ->where('sender_number_id', $sender->id)
                ->value('balance') ?? 0);

            if ($balance >= $requiredCost) {
                $sender->current_balance = $balance;
                $validAlternatives->push($sender);
            }
        }

        return $validAlternatives->sortByDesc('current_balance')->values();
    }

    /**
     * Check advertising balance sufficiency for a sender number, and if insufficient,
     * surface an alternative sender number with sufficient balance if available.
     *
     * @return array{
     *     allowed: bool,
     *     balance: float,
     *     required: float,
     *     alternative_sender: ?SenderNumber,
     *     alternative_senders: Collection<int, SenderNumber>,
     *     error: ?string
     * }
     */
    public function checkAdvertising(
        GhorfeOnlineList $business,
        SenderNumber $senderNumber,
        float $requiredCost
    ): array {
        $advBalance = $this->getAdvertisingBalance($business, $senderNumber);

        return $this->evaluateAdvertising($business, $senderNumber, (float) $advBalance->balance, $requiredCost);
    }

    /**
     * Evaluate a known advertising balance figure against the required cost,
     * surfacing an alternative sender number on shortfall.
     */
    protected function evaluateAdvertising(
        GhorfeOnlineList $business,
        SenderNumber $senderNumber,
        float $balance,
        float $requiredCost
    ): array {
        if ($balance >= $requiredCost) {
            return [
                'allowed' => true,
                'balance' => $balance,
                'required' => $requiredCost,
                'alternative_sender' => null,
                'alternative_senders' => collect(),
                'error' => null,
            ];
        }

        // Insufficient balance on selected sender: look for alternatives
        $alternatives = $this->findAlternativeAdvertisingSenders($business, $requiredCost, $senderNumber->id);
        $primaryAlternative = $alternatives->first();

        if ($primaryAlternative) {
            $error = sprintf(
                'موجودی خط فرستنده %s ناکافی است (موجودی: %s ریال، هزینه: %s ریال). شما می‌توانید از خط جایگزین %s با موجودی %s ریال استفاده نمایید.',
                $senderNumber->number,
                number_format($balance),
                number_format($requiredCost),
                $primaryAlternative->number,
                number_format($primaryAlternative->current_balance)
            );
        } else {
            $error = sprintf(
                'موجودی خط فرستنده %s ناکافی است (موجودی: %s ریال، هزینه: %s ریال) و هیچ خط جایگزین دیگری با موجودی کافی برای این کسب‌وکار یافت نشد.',
                $senderNumber->number,
                number_format($balance),
                number_format($requiredCost)
            );
        }

        return [
            'allowed' => false,
            'balance' => $balance,
            'required' => $requiredCost,
            'alternative_sender' => $primaryAlternative,
            'alternative_senders' => $alternatives,
            'error' => $error,
        ];
    }

    /**
     * Enforce advertising balance and throw InsufficientBalanceException if insufficient,
     * while attaching the surfaced alternative sender number to the exception.
     *
     * @throws InsufficientBalanceException
     */
    public function enforceAdvertising(
        GhorfeOnlineList $business,
        SenderNumber $senderNumber,
        float $requiredCost
    ): AdvertisingBalance {
        // Same locking contract as enforceOtp(): call from within the debit transaction.
        return DB::transaction(function () use ($business, $senderNumber, $requiredCost) {
            $advBalance = $this->getAdvertisingBalance($business, $senderNumber);
            $locked = AdvertisingBalance::query()
                ->whereKey($advBalance->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $check = $this->evaluateAdvertising($business, $senderNumber, (float) $locked->balance, $requiredCost);

            if (!$check['allowed']) {
                throw new InsufficientBalanceException(
                    $check['error'] ?? 'موجودی خط فرستنده تبلیغاتی برای ارسال کافی نیست.',
                    $check['balance'],
                    $requiredCost,
                    $check['alternative_sender']
                );
            }

            return $locked;
        });
    }
}
