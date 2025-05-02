<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\FinancialPlansType;
use App\Models\Service;

class InstallmentController extends Controller
{
    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function showCalculate()
	{
		$services = Service::filter()->get();
        $product_categories = Category::filter()->where('parent_id', 1)->where('isActive' , 1)->get();
        $financial_plans_types = FinancialPlansType::where('status' , true)->where('is_show' , true)->where('payment_type' , '!=' , 'cash')->get();

		return view('site.calculate', compact('financial_plans_types','services','product_categories'));
	}
}
