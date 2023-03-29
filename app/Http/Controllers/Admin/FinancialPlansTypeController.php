<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\Helper;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\FactorItem;
use App\Models\FinancialPlansType;
use App\Models\Service;

class FinancialPlansTypeController extends Controller
{
    public function index()
    {
        $financial_plans_types = FinancialPlansType::all();
        return view('Admin.financialplanstype.all' , compact(['financial_plans_types']));
    }

    public function create()
    {
        $categories = Category::where('isActive' , true)->get();
        $services = Service::where('status' , true)->get();

        return view('Admin.financialplanstype.create' , compact(['categories' , 'services']));
    }
    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $input = $request->all();
        unset($input['service']);
        unset($input['category']);
        unset($input['_token']);
        $input['price'] = (float)str_replace(',', '', Helper::numberConverter($request->input('price')));
        $input['applicant_id'] = auth()->user()->id;
        $input['start_date'] = Helper::vertaToMiladi($request->start_date);
        $input['end_date'] = Helper::vertaToMiladi($request->end_date);
        $financial_plans_type = FinancialPlansType::create($input);
        $financial_plans_type->categories()->attach($request->category);
        $financial_plans_type->services()->attach($request->service);

        return redirect()->route('financialPlansType.index')->with([
            'create' => 'success'
        ]);
    }

    public function edit(FinancialPlansType $financialPlansType)
    {
        $categories = Category::where('isActive' , true)->get();
        $services = Service::where('status' , true)->get();

        return view('Admin.financialplanstype.edit' , compact(['financialPlansType' , 'categories' , 'services']));
    }
    /**
     * @param FinancialPlansType $financialPlansType
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(FinancialPlansType $financialPlansType , Request $request)
    {
        $input = $request->all();
        unset($input['service']);
        unset($input['category']);
        unset($input['_token']);
        $input['price'] = (float)str_replace(',', '', Helper::numberConverter($request->input('price')));
        $input['start_date'] = Helper::vertaToMiladi($request->start_date);
        $input['end_date'] = Helper::vertaToMiladi($request->end_date);

        $financialPlansType->update($input);
        $financialPlansType->categories()->sync($request->category);
        $financialPlansType->services()->sync($request->service);

        return redirect()->route('financialPlansType.index')->with([
            'update' => 'success',
        ]);
    }
    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|void
     * this function is used set financialPlansType by select item // item includs service or category
     */
    public function setFinancialPlansTypeByItem(Request $request)
    {
       if ($request->ajax()) {
            $id = $request->input('id');
            if($request->type == 'category')
            {
                $item = Service::find($id);
            }
            elseif($request->type == 'service')
            {
                $item = Service::find($id);
            }
            if ($item->financialPlansTypes->count() > 0) {
                $financial_plans_types = FinancialPlansType::getFinancialPlansTypeByItem($item);
                $financial_plans_types = $financial_plans_types->map(function ($item) {
                    $temp = [];
                    $temp['id'] = $item->id;
                    $temp['label'] = $item->title . ' - ' . number_format($item->price) . ' ریال';
                    return $temp;
                });
                return response()->json(['count' => 'available', 'financial_plans_types' => $financial_plans_types]);
            }
       }
    }
    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|void
     * this function is used set financialPlansType by select item // item includs service or category
     */
    public function setFinancialPlansTypeByPaymentType(Request $request)
    {
       if ($request->ajax()) {
            $factor_item = FactorItem::find($request->input('factor_item_id'));
            $payment_type = $request->input('payment_type');
            if ($factor_item->factoritemable->financialPlansTypes->count() > 0) {
                $financial_plans_types = FinancialPlansType::getFinancialPlansTypeByItem($factor_item->factoritemable);
                $financial_plans_types = FinancialPlansType::filterFinancialPlansTypeByPaymentType($financial_plans_types , $payment_type);
                $financial_plans_types = $financial_plans_types->map(function ($item) {
                    $temp = [];
                    $temp['id'] = $item->id;
                    $temp['label'] = $item->title . ' - ' . number_format($item->price) . ' ریال';
                    return $temp;
                });
                return response()->json(['count' => 'available', 'financial_plans_types' => $financial_plans_types]);
            }
       }
    }
}

