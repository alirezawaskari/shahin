<?php

namespace App\Services\V1\Admin;

use App\Models\Contract;
use App\Models\ContractUsage;
use App\Models\Employee;
use App\Models\Factor;
use App\Models\FactorItem;
use App\Models\Product;
use App\Models\Role;
use App\Models\Service;
use Illuminate\Support\Collection;

/**
 * Answers «which contract applies here?» for every display point.
 *
 * Every lookup goes through the ContractUsage relation, so nothing is copied into
 * invoices, services or employee records, and a contract can serve several
 * locations at once. Returning the *usage* rather than the contract matters: the
 * contract number and date shown belong to the association, not to the bank
 * contract.
 */
class ContractResolver
{
    /**
     * The active usage for one owner in one location.
     */
    public function forOwner(?string $ownerType, $ownerId, string $location): ?ContractUsage
    {
        if (! ContractUsage::isValidLocation($location) || ! $ownerType || ! $ownerId) {
            return null;
        }

        return ContractUsage::query()
            ->active()
            ->location($location)
            ->for($ownerType, $ownerId)
            ->with('contract')
            ->orderBy('position')
            ->first();
    }

    /**
     * The contract that applies to a whole location (usable_type/usable_id NULL).
     * This is how product purchase invoices get a contract without a per-product screen.
     */
    public function forLocation(string $location): ?ContractUsage
    {
        if (! ContractUsage::isValidLocation($location)) {
            return null;
        }

        return ContractUsage::query()
            ->active()
            ->location($location)
            ->global()
            ->with('contract')
            ->orderBy('position')
            ->first();
    }

    public function forService(Service $service, string $location): ?ContractUsage
    {
        return $this->forOwner(Service::class, $service->id, $location);
    }

    public function forProduct(Product $product, string $location): ?ContractUsage
    {
        return $this->forOwner(Product::class, $product->id, $location);
    }

    /**
     * Registration invoice (فاکتور ثبت‌نام): the contract attached to the service
     * the item was registered for.
     */
    public function forFactorItem(FactorItem $factorItem, string $location = 'registration_invoice'): ?ContractUsage
    {
        // factoritemable is the Service in a registration invoice; other morph
        // targets simply have no service-level contract.
        if ($factorItem->factoritemable_type === Service::class && $factorItem->factoritemable_id) {
            $usage = $this->forOwner(Service::class, $factorItem->factoritemable_id, $location);

            if ($usage) {
                return $usage;
            }
        }

        return $this->forLocation($location);
    }

    /**
     * Purchase invoice (فاکتور خرید): the most specific attachment wins —
     * service-level, then product-level, then the location-wide contract.
     */
    public function forFactor(Factor $factor, string $location = 'purchase_invoice'): ?ContractUsage
    {
        $items = $factor->relationLoaded('factorItems')
            ? $factor->factorItems
            : $factor->factorItems()->get();

        foreach ($items as $item) {
            if ($item->factoritemable_type === Service::class && $item->factoritemable_id) {
                $usage = $this->forOwner(Service::class, $item->factoritemable_id, $location);

                if ($usage) {
                    return $usage;
                }
            }
        }

        foreach ($items as $item) {
            foreach ($item->products as $factorItemProduct) {
                $usage = $this->forOwner(Product::class, $factorItemProduct->product_id, $location);

                if ($usage) {
                    return $usage;
                }
            }
        }

        return $this->forLocation($location);
    }

    /**
     * Employees section (همکاران): the contracts defined for the roles the
     * employee's user holds. An employee can hold several roles, so this returns
     * every matching contract rather than just the first.
     *
     * @return Collection<int, ContractUsage>
     */
    public function forEmployee(Employee $employee): Collection
    {
        $user = $employee->user;

        if (! $user) {
            return collect();
        }

        $roleIds = $user->roles()->pluck('roles.id')->all();

        if (! $roleIds) {
            return collect();
        }

        return ContractUsage::query()
            ->active()
            ->location('employee')
            ->where('usable_type', Role::class)
            ->whereIn('usable_id', $roleIds)
            ->with(['contract', 'usable'])
            ->orderBy('position')
            ->get();
    }

    /**
     * Convenience for the display partials, which only need the contract itself.
     */
    public function contractOf(?ContractUsage $usage): ?Contract
    {
        return $usage?->contract;
    }
}
