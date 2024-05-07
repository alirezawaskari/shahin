<?php

namespace App\Http\Controllers\Admin;

use App\Models\Category;
use App\Models\Employee;
use App\Models\PercentageAllocationEmployee;
use App\Models\Service;
use Illuminate\Http\Request;

class PercentageAllocationEmployeeController extends AdminController
{
    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application|\never
     */
    public function index()
    {
        $user = auth()->user();
        if ($user->isAbleTo('percentageAllocationEmployee_all')) {
            return view('Admin.percentageAllocationEmployee.all');
        }

        return abort(401);
    }
    /**
     * @return \Illuminate\Contracts\View\View|\Illuminate\Contracts\View\Factory
     */
    public function create()
    {
        $service_categories = Category::where('parent_id', 2)->where('isActive' , 1)->get();
        $services = Service::whereStatus(true)->get();
        $employees = Employee::whereStatus(true)->get();

        return view('Admin.percentageAllocationEmployee.create' , compact(['service_categories' , 'services' , 'employees']));
    }

    public function store(Request $request)
    {
        if($request->register_price)
        {
            foreach($request->service as $item)
            {
                PercentageAllocationEmployee::create([
                    'percentage_allocation_employeeable_id' => $item,
                    'percentage_allocation_employeeable_type' => Service::class,
                    'user_id' => $request->employee,
                    'agent_id' => auth()->user()->id,
                    'type' => 'register_price',
                    'percent' => $request->register_price_percent,
                    'start_allocation_date' => $request->startDate,
                    'end_allocation_date' => $request->endDate,
                ]);
            }
        }
        if($request->total_price)
        {
            foreach($request->center as $item)
            {
                PercentageAllocationEmployee::create([
                    'percentage_allocation_employeeable_id' => $item,
                    'percentage_allocation_employeeable_type' => Service::class,
                    'user_id' => $request->employee,
                    'agent_id' => auth()->user()->id,
                    'type' => 'total_price',
                    'percent' => $request->total_price_percent,
                    'start_allocation_date' => $request->startDate,
                    'end_allocation_date' => $request->endDate,
                ]);
            }
        }
        if($request->company_profit)
        {
            foreach($request->center as $item)
            {
                PercentageAllocationEmployee::create([
                    'percentage_allocation_employeeable_id' => $item,
                    'percentage_allocation_employeeable_type' => Service::class,
                    'user_id' => $request->employee,
                    'agent_id' => auth()->user()->id,
                    'type' => 'company_profit',
                    'percent' => $request->company_profit_percent,
                    'start_allocation_date' => $request->startDate,
                    'end_allocation_date' => $request->endDate,
                ]);
            }
        }

        return redirect(route('percentageAllocationEmployee.index'));
    }
}
