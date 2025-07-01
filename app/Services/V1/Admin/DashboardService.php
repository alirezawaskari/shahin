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
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use App\Models\FinancialPlansType;
use App\Models\Factor;
use App\Helpers\Helper;
use Carbon\Carbon;

class DashboardService
{

    protected $representation;

    public function __construct(?Representation $representation = null)
    {
        $this->representation = $representation;
    }
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

    public function getNearEndDateFinancialPlans(): ?Collection
    {
        return Cache::remember('near_end_date_financial_plans', now()->addHours(12), function () {
            return FinancialPlansType::whereDate('end_date', '>=', now()->subDays(10))
                ->whereDate('end_date', '<', now()->addDays(10))
                ->get();
        });
    }

    public function getDashboardData(): array
    {
        $data = [
            'link' => $this->generateLink(),
            'totalUser' => $this->getTotalUsers(),
            'totalDayUser' => $this->getTotalDayUsers(),
            'totalMonthUser' => $this->getTotalMonthUsers(),
            'totalDayFactor' => $this->getTotalDayFactors(),
            'totalMonthFactor' => $this->getTotalMonthFactors(),
            'totalDayFactorPrice' => $this->getTotalDayFactorPrice(),
            'totalDayFactorPriceOrgan' => $this->getTotalDayFactorPriceOrgan(),
            'totalYesterdayFactorPrice' => $this->getTotalYesterdayFactorPrice(),
            'totalWeekFactorPrice' => $this->getTotalWeekFactorPrice(),
            'totalMonthFactorPrice' => $this->getTotalMonthFactorPrice(),
            'totalBeforeMonthFactorPrice' => $this->getTotalBeforeMonthFactorPrice(),
            'totalSixMonthFactorPrice' => $this->getTotalSixMonthFactorPrice(),
            'totalYearFactorPrice' => $this->getTotalYearFactorPrice(),
            'totalAllFactorPrice' => $this->getTotalAllFactorPrice(),
        ];

        return $data;
    }

    private function generateLink(): string
    {
        $user = auth()->user();
        if ($this->representation) {
            return "lalecard.ir/" . $this->representation->slug . '/link/' . $user->id;
        } else {
            return config('app.url') . "/mobile/link/" . $user->id;
        }
    }

    private function getTotalUsers(): int
    {
        $query = User::filter();
        if ($this->representation) {
            $query->where('representation_id', $this->representation->id);
        }
        return $query->count();
    }

    private function getTotalDayUsers(): int
    {
        $query = User::filter()->where('created_at', 'like', date("Y-m-d") . '%');
        if ($this->representation) {
            $query->where('representation_id', $this->representation->id);
        }
        return $query->count();
    }

    private function getTotalMonthUsers(): int
    {
        $query = User::filter()->where('created_at', '>=', Helper::vertaTocarbon(verta()->startMonth()));
        if ($this->representation) {
            $query->where('representation_id', $this->representation->id);
        }
        return $query->count();
    }

    private function getTotalDayFactors(): int
    {
        $query = Factor::filter()->where('created_at', '>', Carbon::now()->subDay())->where('status', '1');
        if ($this->representation) {
            $query->where('representation_id', $this->representation->id);
        }
        return $query->count();
    }

    private function getTotalMonthFactors(): int
    {
        $query = Factor::filter()->where('created_at', '>=', Helper::vertaTocarbon(verta()->startMonth()))->where('status', '1');
        if ($this->representation) {
            $query->where('representation_id', $this->representation->id);
        }
        return $query->count();
    }

    private function getTotalDayFactorPrice(): float
    {
        $query = Factor::filter()->where('created_at', 'like', date("Y-m-d") . '%')->where('status', '1');
        if ($this->representation) {
            $query->where('representation_id', $this->representation->id);
            $query->where('type', '!=', 'organ');
        } else {
            $query->whereIn('type', Factor::getCashPaymentType());
        }
        return $query->sum('price');
    }

    private function getTotalDayFactorPriceOrgan(): float
    {
        $query = Factor::filter()->where('created_at', 'like', date("Y-m-d") . '%')->where('status', '1');
        if ($this->representation) {
            $query->where('representation_id', $this->representation->id)->where('type', 'organ');
        } else {
            $query->where('type', 'credit');
        }
        return $query->sum('price');
    }

    private function getTotalYesterdayFactorPrice(): float
    {
        $startDate = Helper::vertaTocarbon(verta()->subDays(1)->startDay()->addHours(1));
        $endDate = Helper::vertaTocarbon(verta()->subDays(1)->endDay()->addHours(1));
        $query = Factor::filter()->whereBetween('created_at', [$startDate, $endDate])->where('status', '1');
        if (!$this->representation) {
            $query->whereIn('type', Factor::getCashPaymentType());
        }
        return $query->sum('price');
    }

    private function getTotalWeekFactorPrice(): float
    {
        $query = Factor::filter()->where('created_at', '>', Helper::vertaTocarbon(verta()->startWeek()))->where('status', '1');
        if (!$this->representation) {
            $query->whereIn('type', Factor::getCashPaymentType());
        }
        return $query->sum('price');
    }

    private function getTotalMonthFactorPrice(): float
    {
        $query = Factor::filter()->where('created_at', '>', Helper::vertaTocarbon(verta()->startMonth()))->where('status', '1');
        if (!$this->representation) {
            $query->whereIn('type', Factor::getCashPaymentType());
        }
        return $query->sum('price');
    }

    private function getTotalBeforeMonthFactorPrice(): float
    {
        $startDate = Helper::vertaTocarbon(verta()->subMonth(1)->startMonth());
        $endDate = Helper::vertaTocarbon(verta()->subMonth(1)->endMonth()->addDays(1));
        $query = Factor::filter()->whereBetween('created_at', [$startDate, $endDate])->whereStatus(true);
        if (!$this->representation) {
            $query->whereIn('type', Factor::getCashPaymentType());
        }
        return $query->sum('price');
    }

    private function getTotalSixMonthFactorPrice(): float
    {
        $query = Factor::filter()->where('created_at', '>', Helper::vertaTocarbon(verta()->subMonths(6)))->where('status', '1');
        if (!$this->representation) {
            $query->whereIn('type', Factor::getCashPaymentType());
        }
        return $query->sum('price');
    }

    private function getTotalYearFactorPrice(): float
    {
        $query = Factor::filter()->where('created_at', '>', Helper::vertaTocarbon(verta()->startYear()))->where('status', '1');
        if (!$this->representation) {
            $query->whereIn('type', Factor::getCashPaymentType());
        }
        return $query->sum('price');
    }

    private function getTotalAllFactorPrice(): float
    {
        $query = Factor::filter()->where('status', true);
        if (!$this->representation) {
            $query->whereIn('type', Factor::getCashPaymentType());
        }
        return $query->sum('price');
    }



}
