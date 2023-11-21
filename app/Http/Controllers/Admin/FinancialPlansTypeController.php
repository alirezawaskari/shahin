<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\Helper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\FinancialPlansTypeCollection;
use App\Models\Category;
use App\Models\FactorItem;
use App\Models\FinancialPlansType;
use App\Models\Service;
use Illuminate\Support\Facades\Cache;

class FinancialPlansTypeController extends Controller
{
    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function index()
    {
        if (auth()->user()->hasPermission('financial-plans-type'))
        {
            $title = 'فهرست طرح های مالی';
            return view( 'Admin.financialplanstype.all' , compact('title'));
        }
        return abort(401);

    }
    public function getByService(Service $service)
    {
        if (auth()->user()->hasPermission('financial-plans-type'))
        {
            $title = $service->title.'/فهرست طرح های مالی';
            return view( 'Admin.financialplanstype.all' , compact('title' , 'service'));
        }
        return abort(401);

    }
    /**
     * @return \App\Http\Resources\FinancialPlansTypeCollection
     */
    public function filter(Request $request)
    {
        $financial_plans_types = FinancialPlansType::filter()->latest();
        if($request->service)
        {
            $financial_plans_types = $request->service->financialPlansTypes()->filter()->latest();
        }
        if(auth()->user()->admin_representation_id)
        {
            $financial_plans_types = auth()->user()->admin_representation->financialPlansTypes()->filter()->latest();
        }

        return new FinancialPlansTypeCollection($financial_plans_types->paginate(20));
    }
    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function create()
    {
        $categories = Category::where('isActive' , true)->get();
        $services = Service::where('status' , true)->get();

        return view('Admin.financialplanstype.create' , compact(['categories' , 'services']));
    }
    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request)
    {
        Cache::forget('near_end_date_financial_plans');
        $this->validate(request(), [
            'title' => 'required|string',
            'payment_type' => 'required',
            'capacity' => 'required',
        ]);
        $input = $request->all();

        if ($input['payment_type']== 'cash' ||$input['payment_type']== 'installment' ){
            $input['number_pay_steps']=null;
        }
        unset($input['service']);
        unset($input['category']);
        unset($input['_token']);
        unset($input['representation_id']);

        $input['price'] = (float)str_replace(',', '', Helper::numberConverter($request->input('price')));
        $input['applicant_id'] = auth()->user()->id;
        $input['start_date'] = Helper::vertaToMiladi($request->start_date);
        $input['end_date'] = Helper::vertaToMiladi($request->end_date);
        $financial_plans_type = FinancialPlansType::create($input);
        $financial_plans_type->categories()->attach($request->category);
        $financial_plans_type->services()->attach($request->service);
        $representations = auth()->user()->admin_representation_id ? [auth()->user()->admin_representation_id] : $request->input('representation_id');
        $financial_plans_type->representations()->sync($representations);

        return redirect()->route('financialPlansType.index')->with([
            'create' => 'success'
        ]);
    }

    /**
     * @param FinancialPlansType $financialPlansType
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function edit(FinancialPlansType $financialPlansType)
    {
        $categories = Category::where('isActive' , true)->get();
        $services = Service::where('status' , true)->get();

        return view('Admin.financialplanstype.edit' , compact(['financialPlansType' , 'categories' , 'services']));
    }
    /**
     * @param FinancialPlansType $financialPlansType
     * @param Request $request
     * @return RedirectResponse
     */
    public function update(FinancialPlansType $financialPlansType , Request $request)
    {
        $editable = auth()->user()->admin_representation_id ? self::checkExistAnotherRepresentation($financialPlansType) : true;
        if(!$editable)
        {
            return redirect()->route('financialPlansType.index')->with([
                'failByMessage' => 'نمایندگی دیگری از این طرح استفاده می کند . امکان ویرایش این طرح وجود ندارد.',
            ]);
        }
        Cache::forget('near_end_date_financial_plans');
        $input = $request->all();

        if ($input['payment_type']== 'cash' ||$input['payment_type']== 'installment' ){
            $input['number_pay_steps']=null;
        }
        unset($input['service']);
        unset($input['category']);
        unset($input['_token']);
        unset($input['representation_id']);

        $input['price'] = (float)str_replace(',', '', Helper::numberConverter($request->input('price')));
        $input['start_date'] = Helper::vertaToMiladi($request->start_date);
        $input['end_date'] = Helper::vertaToMiladi($request->end_date);

        $financialPlansType->update($input);
        $financialPlansType->categories()->sync($request->category);
        $financialPlansType->services()->sync($request->service);
        !auth()->user()->admin_representation_id ? $financialPlansType->representations()->sync($request->input('representation_id')) : '';

        return redirect()->route('financialPlansType.index')->with([
            'update' => 'success',
        ]);
    }
    /**
     * @param Request $request
     * @return JsonResponse|void
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
     * @return JsonResponse|void
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
    /**
     * @param \App\Models\FinancialPlansType $financialPlansType
     * @return bool
     */
    private function checkExistAnotherRepresentation(FinancialPlansType $financialPlansType)
    {
        if(!$financialPlansType->is_show && $financialPlansType->representations()->count() == 1 && in_array(trim(auth()->user()->admin_representation_id) , $financialPlansType->representations()->pluck('id')->toArray()))
        {
            return true;
        }
        return false;
    }
}

