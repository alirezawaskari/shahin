<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\Helper;
use App\Http\Resources\PercentageAllocationEmployeeCollection;
use App\Models\Category;
use App\Models\Center;
use App\Models\Employee;
use App\Models\PercentageAllocationEmployee;
use App\Models\Service;
use Illuminate\Http\Request;

class PercentageAllocationEmployeeController extends AdminController {
    /**
    * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application|\never
    */
    public function index(Request $request)
    {
        $title = 'فهرست تسهیم';
        $user = auth()->user();
        abort_unless($user->isAbleTo('percentageAllocationEmployee_all'), 401);

        if ($user->hasRole('admin')) {
            $employeesQuery = Employee::filter()->with(['service', 'center', 'user', 'childrenRecursive.user'])
                ->whereNull('deleted_at');
        } else {
            $employeesQuery = Employee::filter()->with(['service', 'center', 'user', 'childrenRecursive.user'])
                ->whereNull('employees.deleted_at')
                ->whereHas('percentageAllocations', function ($q) {
                    $q->whereNull('percentage_allocation_employees.deleted_at');
                });
        }

        if ($request->filled('name')) {
            $employeesQuery->whereHas('user', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->name . '%');
            });
        }

        if ($request->filled('type')) {
            $employeesQuery->whereHas('percentageAllocations', function ($q) use ($request) {
                $q->where('type', $request->type)->whereNull('deleted_at');
            });
        }

        if ($request->filled('startDate')) {
            $employeesQuery->whereDate('contract_start_date', '>=', $request->startDate);
        }

        if ($request->filled('endDate')) {
            $employeesQuery->whereDate('contract_expiration_date', '<=', $request->endDate);
        }

        $employees = $employeesQuery->get();

        $percentageAllocations = PercentageAllocationEmployee::with('user')
            ->whereNull('deleted_at')
            ->whereIn('user_id', $employees->pluck('user_id')->toArray())
            ->get()
            ->groupBy('user_id');

        $delete = $user->isAbleTo('percentageAllocationEmployee_delete');

        return view('Admin.percentageAllocationEmployee.all', compact(
            'employees',
            'percentageAllocations',
            'delete',
            'title'
        ));
    }
    /**
    * @param Request $request
    * @return PercentageAllocationEmployeeCollection|\Illuminate\Http\Response|\Symfony\Component\HttpFoundation\BinaryFileResponse
    */
    public function filter( Request $request ) {
        $percentage_allocation_employees = PercentageAllocationEmployee::filter()->groupBy('user_id')->latest();

        if ( $request->excel == 'on' ) {
            //return ( new Export( $percentage_allocation_employees ) )->download( 'percentage_allocation_employees.xlsx' );
        }
        return new PercentageAllocationEmployeeCollection( $percentage_allocation_employees->paginate( 20 ) );
    }
    /**
    * @return \Illuminate\Contracts\View\View|\Illuminate\Contracts\View\Factory
    */

    public function create() {
        $service_categories = Category::filter()->where( 'parent_id', 2 )->where( 'isActive', 1 )->get();
        $services = Service::filter()->whereStatus( true )->get();
        $employees = Employee::filter()->whereStatus( true )->get();

        return view( 'Admin.percentageAllocationEmployee.create', compact( [ 'service_categories', 'services', 'employees' ] ) );
    }

    public function edit() {
        //
    }

    public function store( Request $request ) {

        if ( auth()->user()->isAbleTo( 'percentageAllocationEmployee_create' ) ) {
            $this->validate( request(), [
                'startDate' => 'required',
                'endDate' => 'required',
                'employee' => 'required',
            ] );

            if ( $request->register_price && $request->service ) {

                foreach ( $request->service as $item ) {
                    PercentageAllocationEmployee::create( [
                        'percentage_allocation_employeeable_id' => $item,
                        'percentage_allocation_employeeable_type' => Service::class,
                        'user_id' => $request->employee,
                        'agent_id' => auth()->user()->id,
                        'type' => 'register_price',
                        'percent' => Helper::numberConverter($request->register_price_percent),
                        'factor_sender' => $request->register_price_factor_sender,
                        'limit_type' => $request->register_price_limit_type,
                        'min_limit_type' => $request->register_price_limit_type == 'count' ? $request->register_price_min_limit_count : $request->register_price_min_limit_price,
                        'max_limit_type' => $request->register_price_limit_type == 'count' ? $request->register_price_max_limit_count : $request->register_price_max_limit_price,
                        'start_allocation_date' => $request->startDate,
                        'end_allocation_date' => $request->endDate,
                    ] );
                }
            }
            if ( $request->total_price && $request->center ) {
                foreach ( $request->center as $item ) {
                    PercentageAllocationEmployee::create( [
                        'percentage_allocation_employeeable_id' => $item,
                        'percentage_allocation_employeeable_type' => Center::class,
                        'user_id' => $request->employee,
                        'agent_id' => auth()->user()->id,
                        'type' => $request->total_price,
                        'percent' => Helper::numberConverter($request->total_price_percent),
                        'factor_sender' => $request->total_price_factor_sender,
                        'limit_type' => $request->total_price_limit_type,
                        'min_limit_type' => $request->total_price_limit_type == 'count' ? $request->total_price_min_limit_count : $request->total_price_min_limit_price,
                        'max_limit_type' => $request->total_price_limit_type == 'count' ? $request->total_price_max_limit_count : $request->total_price_max_limit_price,
                        'start_allocation_date' => $request->startDate,
                        'end_allocation_date' => $request->endDate,
                    ] );
                }
            }
            if ( $request->company_profit && $request->center ) {
                foreach ( $request->center as $item ) {
                    PercentageAllocationEmployee::create( [
                        'percentage_allocation_employeeable_id' => $item,
                        'percentage_allocation_employeeable_type' => Center::class,
                        'user_id' => $request->employee,
                        'agent_id' => auth()->user()->id,
                        'type' => 'company_profit',
                        'percent' => Helper::numberConverter($request->company_profit_percent),
                        'factor_sender' => $request->company_profit_factor_sender,
                        'limit_type' => $request->company_profit_limit_type,
                        'min_limit_type' => $request->company_profit_limit_type == 'count' ? $request->company_profit_min_limit_count : ( float ) str_replace( ',', '', $request->company_profit_min_limit_price ),
                        'max_limit_type' => $request->company_profit_limit_type == 'count' ? $request->company_profit_max_limit_count : ( float ) str_replace( ',', '', $request->company_profit_max_limit_price ),
                        'start_allocation_date' => $request->startDate,
                        'end_allocation_date' => $request->endDate,
                    ] );
                }
            }

            return redirect( route( 'percentageAllocationEmployee.index' ) );
        }
        return abort( 401 );
    }

    /**
     * *
     * @param PercentageAllocationEmployee $percentageAllocationEmployee
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(PercentageAllocationEmployee $percentageAllocationEmployee)
    {
        if (!auth()->user()->hasPermission('percentageAllocationEmployee_delete')) {
            return response()->json([
                'status' => 'error',
                'message' => 'شما مجاز به حذف این مورد نیستید'
            ], 403);
        }

        $percentageAllocationEmployee->delete();

        $stillExists = PercentageAllocationEmployee::where('user_id', $percentageAllocationEmployee->user_id)
            ->whereNull('deleted_at')
            ->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'حذف با موفقیت انجام شد',
            'id' => $percentageAllocationEmployee->id,
            'user_id' => $percentageAllocationEmployee->user_id,
            'still_has_allocations' => $stillExists, // برای کلاینت که بدونه کارمند رو نگه داره یا نه
        ]);
    }


}
