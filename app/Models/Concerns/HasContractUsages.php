<?php

namespace App\Models\Concerns;

use App\Models\Contract;
use App\Models\ContractUsage;

/**
 * Shared by the models a contract can be attached to (Service, Role, Product).
 *
 * The contract is reached only through the ContractUsage relation — nothing is
 * copied onto the owner itself.
 */
trait HasContractUsages
{
    public function contractUsages()
    {
        return $this->morphMany(ContractUsage::class, 'usable');
    }

    /**
     * The active usage for one location, or null when this owner has no contract there.
     */
    public function contractUsageFor(string $location): ?ContractUsage
    {
        return $this->contractUsages()
            ->active()
            ->location($location)
            ->with('contract')
            ->orderBy('position')
            ->first();
    }

    public function contractFor(string $location): ?Contract
    {
        return optional($this->contractUsageFor($location))->contract;
    }
}
