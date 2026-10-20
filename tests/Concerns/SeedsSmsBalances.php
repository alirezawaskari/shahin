<?php

namespace Tests\Concerns;

use App\Models\GhorfeOnlineList;

/**
 * Balances are stored directly on GhorfeOnlineList.
 */
trait SeedsSmsBalances
{
    protected function seedOtpBalance(GhorfeOnlineList|int $business, float $balance, ?float $threshold = null)
    {
        $b = $business instanceof GhorfeOnlineList ? $business : GhorfeOnlineList::findOrFail($business);
        $data = ['otp_balance' => $balance];
        if ($threshold !== null) {
            $data['otp_low_balance_threshold'] = $threshold;
        }
        $b->forceFill($data)->save();
        if ($business instanceof GhorfeOnlineList) {
            $business->otp_balance = $balance;
            if ($threshold !== null) {
                $business->otp_low_balance_threshold = $threshold;
            }
        }

        return $b;
    }

    protected function seedAdvertisingBalance(GhorfeOnlineList|int $business, float $balance)
    {
        $b = $business instanceof GhorfeOnlineList ? $business : GhorfeOnlineList::findOrFail($business);
        $b->forceFill(['advertising_balance' => $balance])->save();
        if ($business instanceof GhorfeOnlineList) {
            $business->advertising_balance = $balance;
        }

        return $b;
    }
}
