<?php

namespace Tests\Concerns;

use App\Models\AdvertisingBalance;
use App\Models\GhorfeOnlineList;
use App\Models\OtpBalance;

/**
 * Balances are deliberately not mass-assignable, so tests seed them explicitly.
 */
trait SeedsSmsBalances
{
    protected function seedOtpBalance(GhorfeOnlineList|int $business, float $balance, ?float $threshold = null): OtpBalance
    {
        $row = OtpBalance::firstOrCreate(['ghorfe_online_list_id' => $business instanceof GhorfeOnlineList ? $business->id : $business]);
        $row->forceFill(array_filter(['balance' => $balance, 'low_balance_threshold' => $threshold], fn ($v) => $v !== null))->save();

        return $row->refresh();
    }

    protected function seedAdvertisingBalance(GhorfeOnlineList|int $business, float $balance): AdvertisingBalance
    {
        $row = AdvertisingBalance::firstOrCreate(['ghorfe_online_list_id' => $business instanceof GhorfeOnlineList ? $business->id : $business]);
        $row->forceFill(['balance' => $balance])->save();

        return $row->refresh();
    }
}
