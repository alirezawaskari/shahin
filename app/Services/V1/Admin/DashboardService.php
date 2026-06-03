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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use App\Models\FinancialPlansType;
use App\Models\Factor;
use App\Helpers\Helper;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    public function getFactorData($representation): array
    {
        if ($representation) {
            $link = "lalecard.ir/".$representation->slug.'/link/'.auth()->user()->id;
            $totalMonthFactorCount = Factor::filter()->where('representation_id', $representation->id)->where('created_at', '>', Helper::vertaTocarbon(verta()->startMonth()))->count();
            $totalMonthFactorPaidCount = Factor::filter()->where('representation_id', $representation->id)->where('created_at', '>', Helper::vertaTocarbon(verta()->startMonth()))->where('status', '1')->count();
            $totalUser = User::filter()->where('representation_id', $representation->id)->count();
            $totalDayUser = User::filter()->filter()->where('representation_id', $representation->id)->where('created_at', 'like', date("Y-m-d").'%')->count();
            $totalMonthUser = User::filter()->where('representation_id', $representation->id)->where('created_at', '>=', Helper::vertaTocarbon(verta()->startMonth()))->count();
            $totalDayFactor = Factor::filter()->where('representation_id', $representation->id)->where('created_at', '>', Carbon::now()->subDay())->where('status', '1')->count();
            $totalMonthFactor = Factor::filter()->where('representation_id', $representation->id)->where('created_at', '>=', Helper::vertaTocarbon(verta()->startMonth()))->where('status', '1')->count();
            $totalDayFactorPrice = Factor::filter()->where('representation_id', $representation->id)->where('created_at', 'like', date("Y-m-d").'%')->where('status', '1')->where('type' ,'!=', 'organ')->sum('price');
            $totalDayFactorPriceOrgan = Factor::filter()->where('representation_id', $representation->id)->where('created_at', 'like', date("Y-m-d").'%')->where('status', '1')->where('type','organ')->sum('price');
            $totalYesterdayFactorPrice = Factor::filter()->where('representation_id', $representation->id)->whereBetween('created_at',[Helper::vertaTocarbon(verta()->subDays(1)->startDay()->addHours(1)), Helper::vertaTocarbon(verta()->subDays(1)->endDay()->addHours(1))])->where('status', '1')->sum('price');
            $totalWeekFactorPrice = Factor::filter()->filter()->where('representation_id', $representation->id)->where('created_at', '>', Helper::vertaTocarbon(verta()->startWeek()))->where('status', '1')->sum('price');
            $totalMonthFactorPrice = Factor::filter()->where('representation_id', $representation->id)->where('created_at', '>', Helper::vertaTocarbon(verta()->startMonth()))->where('status', '1')->sum('price');
            $totalBeforeMonthFactorPrice = Factor::filter()->where('representation_id', $representation->id)->whereBetween('created_at', [Helper::vertaTocarbon(verta()->subMonth(1)->startMonth()), Helper::vertaTocarbon(verta()->subMonth(1)->endMonth()->addDays(1))])->whereStatus(true)->sum('price');
            $totalSixMonthFactorPrice = Factor::filter()->where('representation_id', $representation->id)->where('created_at', '>', Helper::vertaTocarbon(verta()->subMonths(6)))->where('status', '1')->sum('price');
            $totalYearFactorPrice =  Factor::filter()->where('representation_id', $representation->id)->where('created_at', '>', Helper::vertaTocarbon(verta()->startYear()))->where('status', '1')->sum('price');
            $totalAllFactorPrice = Factor::filter()->where('representation_id', $representation->id)->where('status', true)->sum('price');
        }
        else
        {
            $link = config('app.url')."/mobile/link/".auth()->user()->id;
            $totalMonthFactorCount = Factor::filter()->where('created_at', '>', Helper::vertaTocarbon(verta()->startMonth()))->count();
            $totalMonthFactorPaidCount = Factor::filter()->where('created_at', '>', Helper::vertaTocarbon(verta()->startMonth()))->where('status', '1')->count();
            $totalUser = User::filter()->count();
            $totalDayUser = User::filter()->where('created_at', 'like', date("Y-m-d").'%')->count();
            $totalMonthUser = User::filter()->where('created_at', '>=', Helper::vertaTocarbon(verta()->startMonth()))->count();
            $totalDayFactor = Factor::filter()->where('created_at', '>', Carbon::now()->subDay())->where('status', '1')->count();
            $totalMonthFactor = Factor::filter()->where('created_at', '>=', Helper::vertaTocarbon(verta()->startMonth()))->where('status', '1')->count();
            $totalDayFactorPrice = Factor::filter()->where('created_at', 'like', date("Y-m-d").'%')->where('status', '1')->whereIn('type' , Factor::getCashPaymentType())->sum('price');
            $totalDayFactorPriceOrgan = Factor::filter()->where('created_at', 'like', date("Y-m-d").'%')->where('status', '1')->where('type' , 'credit')->sum('price');
            $totalYesterdayFactorPrice = Factor::filter()->whereBetween('created_at',[Helper::vertaTocarbon(verta()->subDays(1)->startDay()->addHours(1)), Helper::vertaTocarbon(verta()->subDays(1)->endDay()->addHours(1))])->where('status', '1')->whereIn('type' , Factor::getCashPaymentType())->sum('price');
            $totalWeekFactorPrice = Factor::filter()->filter()->where('created_at', '>', Helper::vertaTocarbon(verta()->startWeek()))->where('status', '1')->whereIn('type' , Factor::getCashPaymentType())->sum('price');
            $totalMonthFactorPrice = Factor::filter()->where('created_at', '>', Helper::vertaTocarbon(verta()->startMonth()))->where('status', '1')->whereIn('type' , Factor::getCashPaymentType())->sum('price');
            $totalBeforeMonthFactorPrice = Factor::filter()->whereBetween('created_at', [Helper::vertaTocarbon(verta()->subMonth(1)->startMonth()), Helper::vertaTocarbon(verta()->subMonth(1)->endMonth()->addDays(1))])->whereStatus(true)->whereIn('type' , Factor::getCashPaymentType())->sum('price');
            $totalSixMonthFactorPrice = Factor::filter()->where('created_at', '>', Helper::vertaTocarbon(verta()->subMonths(6)))->where('status', '1')->whereIn('type' , Factor::getCashPaymentType())->sum('price');
            $totalYearFactorPrice =  Factor::filter()->where('created_at', '>', Helper::vertaTocarbon(verta()->startYear()))->where('status', '1')->whereIn('type' , Factor::getCashPaymentType())->sum('price');
            $totalAllFactorPrice = Factor::filter()->where('status', true)->whereIn('type' , Factor::getCashPaymentType())->sum('price');
        }
        if(auth()->user()->hasPermission('view-sales-revenue'))
        {
            $totalMonthFactorCount = Factor::filter()->where('created_at', '>', Helper::vertaTocarbon(verta()->startMonth()))->count();
            $totalMonthFactorPaidCount = Factor::filter()->where('created_at', '>', Helper::vertaTocarbon(verta()->startMonth()))->where('status', '1')->count();
            $totalUser = User::filter()->count();
            $totalDayUser = User::filter()->where('created_at', '>', Carbon::now()->subDay())->count();
            $totalMonthUser = User::filter()->where('created_at', '>=', Helper::vertaTocarbon(verta()->startMonth()))->count();
            $totalDayFactor = Factor::filter()->where('created_at', '>', Carbon::now()->subDay())->where('status', '1')->count();
            $totalMonthFactor = Factor::filter()->where('created_at', '>=', Helper::vertaTocarbon(verta()->startMonth()))->where('status', '1')->count();
            $totalDayFactorPrice = Factor::filter()->where('created_at', 'like', date("Y-m-d").'%')->where('status', '1')->whereIn('type' , Factor::getCashPaymentType())->sum('price');
            $totalDayFactorPriceOrgan = Factor::filter()->where('created_at', 'like', date("Y-m-d").'%')->where('status', '1')->where('type' , 'credit')->sum('price');
            $totalYesterdayFactorPrice = Factor::filter()->whereBetween('created_at',[Helper::vertaTocarbon(verta()->subDays(1)->startDay()->addHours(1)), Helper::vertaTocarbon(verta()->subDays(1)->endDay()->addHours(1))])->where('status', '1')->whereIn('type' , Factor::getCashPaymentType())->sum('price');
            $totalWeekFactorPrice = Factor::filter()->where('created_at', '>', Helper::vertaTocarbon(verta()->startWeek()))->where('status', '1')->whereIn('type' , Factor::getCashPaymentType())->sum('price');
            $totalMonthFactorPrice = Factor::filter()->where('created_at', '>', Helper::vertaTocarbon(verta()->startMonth()))->where('status', '1')->whereIn('type' , Factor::getCashPaymentType())->sum('price');
            $totalBeforeMonthFactorPrice = Factor::filter()->whereBetween('created_at', [Helper::vertaTocarbon(verta()->subMonth(1)->startMonth()), Helper::vertaTocarbon(verta()->subMonth(1)->endMonth()->addDays(1))])->whereStatus(true)->whereIn('type' , Factor::getCashPaymentType())->sum('price');
            $totalSixMonthFactorPrice = Factor::filter()->where('created_at', '>', Helper::vertaTocarbon(verta()->subMonths(6)))->where('status', '1')->whereIn('type' , Factor::getCashPaymentType())->sum('price');
            $totalYearFactorPrice =  Factor::filter()->where('created_at', '>', Helper::vertaTocarbon(verta()->startYear()))->where('status', '1')->whereIn('type' , Factor::getCashPaymentType())->sum('price');
            $totalAllFactorPrice = Factor::filter()->where('status', true)->whereIn('type' , Factor::getCashPaymentType())->sum('price');
        }
        if(auth()->user()->center_id)
        {
            $totalUser = User::where('center_id' , auth()->user()->id)->count();
            $totalDayUser = User::where('center_id' , auth()->user()->id)->where('created_at', '>', Carbon::now()->subDay())->count();
            $totalMonthUser = User::where('center_id' , auth()->user()->id)->where('created_at', '>=', Helper::vertaTocarbon(verta()->startMonth()))->count();
            $totalDayFactor = Factor::where('center_id' , auth()->user()->center_id)->where('created_at', '>', Carbon::now()->subDay())->where('status', '1')->count();
            $totalMonthFactor = Factor::where('center_id' , auth()->user()->center_id)->where('created_at', '>=', Helper::vertaTocarbon(verta()->startMonth()))->where('status', '1')->count();
            $totalDayFactorPrice = Factor::where('center_id' , auth()->user()->center_id)->where('created_at', 'like', date("Y-m-d").'%')->where('status', '1')->whereIn('type' , Factor::getCashPaymentType())->with('factor_items')->sum('price');
            $totalDayFactorPriceOrgan = Factor::where('center_id' , auth()->user()->center_id)->where('created_at', 'like', date("Y-m-d").'%')->where('status', '1')->where('type' , 'credit')->sum('price');
            $totalYesterdayFactorPrice = Factor::where('center_id' , auth()->user()->center_id)->whereBetween('created_at',[Helper::vertaTocarbon(verta()->subDays(1)->startDay()->addHours(1)), Helper::vertaTocarbon(verta()->subDays(1)->endDay()->addHours(1))])->where('status', '1')->whereIn('type' , Factor::getCashPaymentType())->sum('price');
            $totalWeekFactorPrice = Factor::where('center_id' , auth()->user()->center_id)->where('created_at', '>', Helper::vertaTocarbon(verta()->startWeek()))->where('status', '1')->whereIn('type' , Factor::getCashPaymentType())->sum('price');
            $totalMonthFactorPrice = Factor::where('center_id' , auth()->user()->center_id)->where('created_at', '>', Helper::vertaTocarbon(verta()->startMonth()))->where('status', '1')->whereIn('type' , Factor::getCashPaymentType())->sum('price');
            $totalBeforeMonthFactorPrice = Factor::where('center_id' , auth()->user()->center_id)->whereBetween('created_at', [Helper::vertaTocarbon(verta()->subMonth(1)->startMonth()), Helper::vertaTocarbon(verta()->subMonth(1)->endMonth()->addDays(1))])->whereStatus(true)->whereIn('type' , Factor::getCashPaymentType())->sum('price');
            $totalSixMonthFactorPrice = Factor::where('center_id' , auth()->user()->center_id)->where('created_at', '>', Helper::vertaTocarbon(verta()->subMonths(6)))->where('status', '1')->whereIn('type' , Factor::getCashPaymentType())->sum('price');
            $totalYearFactorPrice =  Factor::where('center_id' , auth()->user()->center_id)->where('created_at', '>', Helper::vertaTocarbon(verta()->startYear()))->where('status', '1')->whereIn('type' , Factor::getCashPaymentType())->sum('price');
            $totalAllFactorPrice = Factor::where('center_id' , auth()->user()->center_id)->where('status', true)->whereIn('type' , Factor::getCashPaymentType())->sum('price');
        }

        return [
            'link' => $link,
            'totalMonthFactorCount' => $totalMonthFactorCount,
            'totalMonthFactorPaidCount' => $totalMonthFactorPaidCount,
            'totalUser' => $totalUser,
            'totalDayUser' => $totalDayUser,
            'totalMonthUser' => $totalMonthUser,
            'totalDayFactor' => $totalDayFactor,
            'totalMonthFactor' => $totalMonthFactor,
            'totalDayFactorPrice' => $totalDayFactorPrice,
            'totalDayFactorPriceOrgan' => $totalDayFactorPriceOrgan,
            'totalYesterdayFactorPrice' => $totalYesterdayFactorPrice,
            'totalWeekFactorPrice' => $totalWeekFactorPrice,
            'totalMonthFactorPrice' => $totalMonthFactorPrice,
            'totalBeforeMonthFactorPrice' => $totalBeforeMonthFactorPrice,
            'totalSixMonthFactorPrice' => $totalSixMonthFactorPrice,
            'totalYearFactorPrice' => $totalYearFactorPrice,
            'totalAllFactorPrice' => $totalAllFactorPrice,
        ];
    }
    public function getDetailData($representation): array
    {
        $factor_month = ['success' => [], 'unsuccess' => []];
        $factor_week = ['success' => [], 'unsuccess' => []];
        $user_week = [];
        $user_month = [];

        $representation_id = $representation?->id;
        $hasPermission = auth()->user()->hasPermission('view-sales-revenue');

        $monthRange = fn($i) => [
            Helper::vertaTocarbon(verta()->subMonths($i)->startMonth()),
            Helper::vertaTocarbon(verta()->subMonths($i)->endMonth())
        ];

        $dayDate = fn($i) => Helper::vertaTocarbon(verta()->subDays($i));

        $factorQuery = fn($dateRange, $success, $isMonth = true) =>
        Factor::filter()
            ->when($representation_id, fn($q) => $q->where('representation_id', $representation_id))
            ->when($hasPermission, fn($q) => $q) // در اینجا می‌توان شرط خاصی اضافه کرد
            ->when($success, fn($q) => $q->whereStatus(true), fn($q) => $q->where('status', '!=', 1))
            ->when($isMonth, fn($q) => $q->whereBetween('created_at', $dateRange), fn($q) => $q->whereDate('created_at', $dateRange))
            ->when(true, fn($q) => $q->whereIn('type', Factor::getCashPaymentType()))
            ->sum('price');
        $userQuery = fn($dateRange, $isMonth = true) =>
        User::filter()
            ->when($representation_id, fn($q) => $q->where('representation_id', $representation_id))
            ->when($isMonth, fn($q) => $q->whereBetween('created_at', $dateRange), fn($q) => $q->whereDate('created_at', $dateRange))
            ->count();

        for ($i = 0; $i <= 6; $i++) {
            $range = $monthRange($i);
            $factor_month['success'][$i] = $factorQuery($range, true);
            $factor_month['unsuccess'][$i] = $factorQuery($range, false);
            $user_month[$i] = $userQuery($range);
        }

        for ($i = 0; $i <= 6; $i++) {
            $date = $dayDate($i);
            $factor_week['success'][$i] = $factorQuery($date, true, false);
            $factor_week['unsuccess'][$i] = $factorQuery($date, false, false);
            $user_week[$i] = $userQuery($date, false);
        }

        $factor_month['success'] = array_reverse($factor_month['success']);
        $factor_month['unsuccess'] = array_reverse($factor_month['unsuccess']);
        $factor_week['success'] = array_reverse($factor_week['success']);
        $factor_week['unsuccess'] = array_reverse($factor_week['unsuccess']);
        $user_month = array_reverse($user_month);
        $user_week = array_reverse($user_week);
        return [
            'factor_month' => $factor_month,
            'factor_week' => $factor_week,
            'user_month' => $user_month,
            'user_week' => $user_week,
        ];
    }
    public function getServicesData($services,$representation): array
    {
        $start_date = "";
        $final_date = date("Y-m-d");
        $service_count = [];
        $service_name = [];

        $callcenter_id = auth()->user()->id;
        $representation_id = $representation ? $representation->id : null;

        if (auth()->user()->admin_organization_id) {
            $services = auth()->user()->admin_organization->services;

            foreach ($services as $service) {
                $service_id = $service->id;

                $totalCount = DB::table('factors')
                    ->join('factor_items', 'factors.id', '=', 'factor_items.factor_id')
                    ->where('factor_items.factoritemable_id', $service_id)
                    ->where('factors.status', 1)
                    ->count();

                if ($totalCount >= 50) {
                    $count = DB::table('factors')
                        ->join('factor_items', 'factors.id', '=', 'factor_items.factor_id')
                        ->where('factor_items.factoritemable_id', $service_id)
                        ->where('factors.status', 1)
                        ->where('factors.organization_id', auth()->user()->admin_organization_id)
                        ->whereBetween('factors.created_at', [$start_date, $final_date])
                        ->count();

                    $service_count[] = $count;
                    $service_name[] = $service->title;
                }
            }
        } else {
            foreach ($services as $service) {
                $service_id = $service->id;

                $totalCount = DB::table('factors')
                    ->join('factor_items', 'factors.id', '=', 'factor_items.factor_id')
                    ->where('factor_items.factoritemable_id', $service_id)
                    ->where('factors.status', 1)
                    ->count();

                if ($totalCount >= 50) {
                    $query = DB::table('factors')
                        ->join('factor_items', 'factors.id', '=', 'factor_items.factor_id')
                        ->where('factor_items.factoritemable_id', $service_id)
                        ->where('factors.status', 1);

                    if ($callcenter_id) {
                        $query->where('callcenter_id', $callcenter_id);
                    } elseif ($representation_id) {
                        $query->where('representation_id', $representation_id);
                    }

                    $count = $query
                        ->whereBetween('factors.created_at', [$start_date, $final_date])
                        ->count();

                    $service_count[] = $count;
                    $service_name[] = $service->title;
                }
            }
        }

        return [
            'service_count' => $service_count,
            'service_name' => $service_name
        ];

    }
}
