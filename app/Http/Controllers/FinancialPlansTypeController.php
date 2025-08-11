<?php

namespace App\Http\Controllers;

use App\Models\FinancialPlansType;

class FinancialPlansTypeController extends Controller
{
    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function index()
    {
        $financial_plans_types = FinancialPlansType::where('status' , 1)->where('is_show_loan_list' , 1)->whereRaw('capacity > reserved')->where('end_date' , '>=' , now())->latest()->get()->unique();
        return view('site.financialPlansType.index' , compact('financial_plans_types'));
    }
}
