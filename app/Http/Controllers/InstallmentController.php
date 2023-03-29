<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\FinancialPlansType;
use App\Models\Service;

class InstallmentController extends Controller
{
    public function showCalculate()
	{
		$services = Service::all();
        $product_categories = Category::where('parent_id', 1)->where('isActive' , 1)->get();
        $financial_plans_types = FinancialPlansType::where('status' , true)->where('is_show' , true)->where('payment_type' , '!=' , 'cash')->get();

		return view('site.calculate', compact('financial_plans_types','services','product_categories'));
	}
}
