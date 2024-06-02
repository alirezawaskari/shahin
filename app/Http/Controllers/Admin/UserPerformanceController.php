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
use App\Models\UserPerformance;
use Illuminate\Http\Request;

class UserPerformanceController extends AdminController {
    /**
    * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application|\never
    */
    public function report() {
        $user = auth()->user();
        if ( $user->isAbleTo( 'percentageAllocationEmployee_report' ) ) {
            $employees = Employee::whereStatus( true )->get();

            return view( 'Admin.percentageAllocationEmployee.report', compact( 'employees' ) );
        }

        return abort( 401 );
    }
    /**
    * @param Request $request
    * @return PercentageAllocationEmployeeCollection|\Illuminate\Http\Response|\Symfony\Component\HttpFoundation\BinaryFileResponse
    */
    public function reportFilter( Request $request ) {
        $percentage_allocation_employee_reports = UserPerformance::filter()->latest();

        if ( $request->excel == 'on' ) {
            //return ( new Export( $percentage_allocation_employees ) )->download( 'percentage_allocation_employees.xlsx' );
        }
        return new PercentageAllocationEmployeeReportCollection( $percentage_allocation_employee_reports->paginate( 20 ) );
    }

}
