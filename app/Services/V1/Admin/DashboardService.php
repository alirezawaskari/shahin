<?php

namespace App\Services\V1\Admin;


use App\Http\Controllers\Admin\ContextController;
use App\Models\FactorItem;
use App\Models\TotalFactorItem;
use App\Models\User;
use App\Models\PurchaseInvoice;
use App\Models\Resume;
use App\Models\Role;
use App\Models\Representation;
use Illuminate\Support\Facades\Cache;
use App\Models\FinancialPlansType;

class DashboardService
{
    public function getUserRole(User $user)
    {
        return $user->roles->first();
    }

    public function getUserRepresentation(User $user)
    {
        return $user->admin_representation;
    }

    public function getUserCenterId(User $user): ?int
    {
        return $user->center_id;
    }
    public function getUnprocessedTicketCount(User $user): int
    {
        return $user->tickets->where('status', false)->count();
    }
    public function getResumeCount(Role $role, ?Representation $representation = null): int
    {
        $query = Resume::query();

        if ($representation) {
            $query->where('representation_id', $representation->id);
        }

        $query->where('card_id', $role->card_id)
            ->whereNotIn('status', [3, 4]);

        return $query->count();
    }
    public function getFactorItemConfirmCount(User $user): int
    {
        $centerId = $user->center_id;
        return FactorItem::where('status_factor', 10)
            ->where('center_id', $centerId)
            ->count();
    }
    public function getFactorCounts(User $user, ?Representation $representation = null): array
    {
        $counts = [
            'count_factor_supplier' => 0,
            'count_factor_mali' => 0,
            'count_factor_superadmin' => 0,
            'count_factor_warehouseKeeper' => 0,
            'count_factoritem_mali' => 0,
            'count_factoritem_consulting_contract_affairs' => 0,
            'count_factoritem_mali_legal' => 0,
            'count_factoritem_mali_mali' => 0,
            'count_factoritem_mali_superadmin' => 0,
            'count_factoritem_superadmin' => 0,
            'count_factoritem_filing' => 0,
        ];
        if ($representation) {
            $counts['count_factor_mali_representation'] = PurchaseInvoice::where('status', 1)
                ->where('representation_id', $representation->id)
                ->count();
            $counts['count_factor_superadmin_representation'] = PurchaseInvoice::where('status', 2)
                ->where('representation_id', $representation->id)
                ->count();
            $counts['count_factoritem_mali_representation'] = TotalFactorItem::where('total_factor_item_status_id', 11)->count();
            $counts['count_factoritem_superadmin_representation'] = TotalFactorItem::where('total_factor_item_status_id', 12)->count();
            $counts['count_factoritem_filing'] = FactorItem::where('status_factor', 9)->count();
        } else {
            $counts['count_factor_supplier'] = $user->supplier
                ? PurchaseInvoice::where('supplier_id', $user->supplier->id)->where('status', 0)->count()
                : 0;
            $counts['count_factor_mali'] = PurchaseInvoice::where('status', 1)->count();
            $counts['count_factor_superadmin'] = PurchaseInvoice::where('status', 2)->count();
            $counts['count_factor_warehouseKeeper'] = PurchaseInvoice::where('status', 6)->count();
            $counts['count_factoritem_mali'] = TotalFactorItem::where('total_factor_item_status_id', 5)->count();
            $counts['count_factoritem_consulting_contract_affairs'] = TotalFactorItem::where('total_factor_item_status_id', 13)->count();
            $counts['count_factoritem_mali_legal'] = TotalFactorItem::where('status_mali', 10)->count();
            $counts['count_factoritem_mali_mali'] = TotalFactorItem::whereIn('status_mali', [7, 9])->count();
            $counts['count_factoritem_mali_superadmin'] = TotalFactorItem::where('status_mali', 8)->count();
            $counts['count_factoritem_superadmin'] = TotalFactorItem::where('total_factor_item_status_id', 8)->count();
            $counts['count_factoritem_filing'] = TotalFactorItem::where('total_factor_item_status_id', 9)->count();
        }
        return $counts;
    }
    public function getExpireDates(): ?array
    {
        return Cache::remember('expire_dates', now()->addHours(12), function () {
            return ContextController::fetchExpireDates();
        });
    }

    public function getNearEndDateFinancialPlans(): ?\Illuminate\Database\Eloquent\Collection
    {
        return Cache::remember('near_end_date_financial_plans', now()->addHours(12), function () {
            return FinancialPlansType::whereDate('end_date', '>=', now()->subDays(10))
                ->whereDate('end_date', '<', now()->addDays(10))
                ->get();
        });
    }
}
