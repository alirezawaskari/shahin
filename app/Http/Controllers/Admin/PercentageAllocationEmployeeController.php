<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\Helper;
use App\Http\Resources\PercentageAllocationEmployeeCollection;
use App\Http\Resources\PercentageAllocationEmployeeReportCollection;
use App\Models\Category;
use App\Models\Center;
use App\Models\Employee;
use App\Models\PercentageAllocationEmployee;
use App\Models\PercentageAllocationEmployeeUserPerformance;
use App\Models\Service;
use Illuminate\Http\Request;

class PercentageAllocationEmployeeController extends AdminController {
    /**
    * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application|\never
    */
    public function index() {
        $user = auth()->user();
        if ( $user->isAbleTo( 'percentageAllocationEmployee_all' ) ) {
            $delete = false;
            $employees = Employee::whereStatus( true )->get();
            if ( auth()->user()->isAbleTo( 'percentageAllocationEmployee_delete' ) ) {
                $delete = true;
            }
            return view( 'Admin.percentageAllocationEmployee.all', compact( 'employees', 'delete' ) );
        }

        return abort( 401 );
    }
    /**
    * @param Request $request
    * @return PercentageAllocationEmployeeCollection|\Illuminate\Http\Response|\Symfony\Component\HttpFoundation\BinaryFileResponse
    */
    public function filter( Request $request ) {
        $percentage_allocation_employees = PercentageAllocationEmployee::filter()->latest();

        if ( $request->excel == 'on' ) {
            //return ( new Export( $percentage_allocation_employees ) )->download( 'percentage_allocation_employees.xlsx' );
        }
        return new PercentageAllocationEmployeeCollection( $percentage_allocation_employees->paginate( 20 ) );
    }
    /**
    * @return \Illuminate\Contracts\View\View|\Illuminate\Contracts\View\Factory
    */

    public function create() {
        $service_categories = Category::where( 'parent_id', 2 )->where( 'isActive', 1 )->get();
        $services = Service::whereStatus( true )->get();
        $employees = Employee::whereStatus( true )->get();

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
     * delete resume
     * @param PercentageAllocationEmployee $percentageAllocationEmployee
     * @return PercentageAllocationEmployee
     */
    public function destroy(PercentageAllocationEmployee $percentageAllocationEmployee)
    {
        if (auth()->user()->hasPermission('percentageAllocationEmployee_delete'))
        {
           $percentageAllocationEmployee->delete();
            return response(['status'=>'success']);
        }
            return response(['status'=>'error']);

    }
}
