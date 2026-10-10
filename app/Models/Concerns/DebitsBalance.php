<?php

namespace App\Models\Concerns;

use App\Exceptions\Sms\InsufficientBalanceException;

/**
 * Race-safe balance debit for OtpBalance / AdvertisingBalance.
 *
 * Must be called inside a DB transaction: the row lock taken here is held
 * until that transaction commits or rolls back, so a concurrent send cannot
 * observe the same balance and overspend it.
 */
trait DebitsBalance
{
    /**
     * Lock this balance row, verify it covers $amount, and debit it atomically.
     *
     * @return float The balance before the debit.
     *
     * @throws InsufficientBalanceException
     */
    public function lockAndDebit(float $amount): float
    {
        $locked = static::query()
            ->whereKey($this->getKey())
            ->lockForUpdate()
            ->firstOrFail();

        $before = (float) $locked->balance;

        if ($before < $amount) {
            throw $this->insufficientBalance($before, $amount);
        }

        // Guarded update: only succeeds if the balance still covers the amount.
        $affected = static::query()
            ->whereKey($this->getKey())
            ->where('balance', '>=', $amount)
            ->decrement('balance', $amount);

        if ($affected !== 1) {
            throw $this->insufficientBalance($before, $amount);
        }

        $this->balance = $before - $amount;

        return $before;
    }

    private function insufficientBalance(float $balance, float $amount): InsufficientBalanceException
    {
        return new InsufficientBalanceException(
            sprintf(
                'موجودی ناکافی است. موجودی فعلی: %s ریال، هزینه پیامک: %s ریال.',
                number_format($balance),
                number_format($amount)
            ),
            $balance,
            $amount
        );
    }
}
