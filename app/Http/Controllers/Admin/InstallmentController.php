<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Installment;
use App\Models\Service;
use Illuminate\Http\Request;

class InstallmentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function createCategory($id)
    {
        $installmentable_id = $id;
        $installmentable_type = Category::class;
        return view('Admin.installment.create', ['installmentable_id' => $installmentable_id,
            'installmentable_type' => $installmentable_type]);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */

    public function store(Request $request)
    {
        $request->validate([
            'num' => 'required',
            'high_interval' => 'required',
            'down_interval' => 'required',
        ],
            [
                'num.required' => 'لطفا تعداد را وارد کنید',
                'high_interval.required' => 'لطفا حد بالا وارد کنید',
                'down_interval.required' => 'لطفا حد پایین وارد کنید'
            ]
        );
        $request['high_interval'] = str_replace(",", "", $request->high_interval);
        $request['down_interval'] = str_replace(",", "", $request->down_interval);
        $installment = Installment::create($request->all());

        if ($installment->installmentable_type == Category::class) {
            return redirect('/dashboard/installment/' . $installment->installmentable_id)->with(['create' => 'success']);
        } else if ($installment->installmentable_type == Service::class) {
            return redirect('/dashboard/installment/service/' . $installment->installmentable_id)->with(['create' => 'success']);
        }

        return redirect()->back();
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //lists of category-installment
        $category = Category::select('id', 'title')->where('id', $id)->first();
        $parent_id = Category::findCategory($id, 'parent_id');
        $installments = $category->installments()->paginate(15);

        return view('Admin.installment.lists', ['installments' => $installments,
            'category' => $category, 'parent_id' => $parent_id, 'id' => $id]);

    }

    /**
     * @param $id
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function createService($id)
    {
        $installmentable_id = $id;
        $installmentable_type = Service::class;
        return view('Admin.installment.create', ['installmentable_id' => $installmentable_id,
            'installmentable_type' => $installmentable_type]);
    }

    /**
     * @param $id
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function serviceInstallment($id)
    {
        //lists of service-installment
        $service = Service::select('id', 'title')->where('id', $id)->first();
        $installments = $service->installments()->paginate(15);

        return view('Admin.installment.lists', ['service' => $service, 'installments' => $installments, 'id' => $id]);
    }

    /**
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Foundation\Application|\Illuminate\View\View
     */
    public function serviceInstallmentDetail($id)
    {
        $service = Service::where('id', $id)->first();
        $installments = $service->installments()->get();
        $installment_details=  $this->serviceInstallmentDetailSetItems($installments, $service);

        return view('Admin.installment.installmentDetails', compact('installment_details'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if ($id) {
            $installment = Installment::find($id);
            if ($installment) {
                return view('Admin.installment.edit', ['installment' => $installment]);
            }
        }
        return redirect()->back();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'num' => 'required',
            'high_interval' => 'required',
            'down_interval' => 'required',
        ],
            [
                'num.required' => 'لطفا تعداد را وارد کنید',
                'high_interval.required' => 'لطفا حد بالا وارد کنید',
                'down_interval.required' => 'لطفا حد پایین وارد کنید'
            ]
        );
        $request['high_interval'] = str_replace(",", "", $request->high_interval);
        $request['down_interval'] = str_replace(",", "", $request->down_interval);
        $installment = Installment::find($id);
        $installment->update($request->all());

        $percent = $installment->installmentable->percent;

        $installments_num = $installment->num;

        if ($percent != 0) {
            $prepayment_down = $installment->down_interval * ($percent / 100);
            $prepayment_high = $installment->high_interval * ($percent / 100);
        } else {
            $prepayment_down = 0;
            $prepayment_high = 0;
        }

        $company_profit_down = ($installment->down_interval - $prepayment_down) * 0.04 / 12 * $installments_num;
        $company_profit_high = ($installment->high_interval - $prepayment_high) * 0.04 / 12 * $installments_num;

        $value_added_down = $company_profit_down * 0.09;
        $value_added_high = $company_profit_high * 0.09;

        $facilities_down = $installment->down_interval - $prepayment_down + $company_profit_down + $value_added_down;
        $facilities_high = $installment->high_interval - $prepayment_high + $company_profit_high + $value_added_high;


        $amount_installment_down_interval = $facilities_down / $installments_num;
        $amount_installment_high_interval = $facilities_high / $installments_num;

        $installment->down_installment_price = $amount_installment_down_interval;
        $installment->high_installment_price = $amount_installment_high_interval;
        $installment->save();


        if ($installment->installmentable_type == Category::class) {
            return redirect('/dashboard/installment/' . $installment->installmentable_id)->with(['update' => 'success']);
        } else {
            return redirect('/dashboard/installment/service/' . $installment->installmentable_id)->with(['update' => 'success']);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function finishInstallment(Request $request)
    {
        if (isset($request->service_id)) {
            $record = Service::find($request->service_id);
            $installments = $record->installments;
        }
        if (isset($request->category_id)) {
            $record = Category::find($request->category_id);
            $installments = $record->installments;
        }
        foreach ($installments as $installment) {
            $high = $installment->high_interval;
            $down = $installment->down_interval;

            if (isset($record) && $record != null) {

                if (isset($high) && $high != null) {
                    $prepayment = $high * ($record->percent / 100);
                    $installments_num = Installment::getInstallmentNum($high, $record);
                    $wage = ($high - $prepayment) * 0.04 / 12 * $installments_num;
                    $tax = $wage * 0.09;
                    $loan = $high - $prepayment + $wage + $tax;
                    $installments_price = $loan / $installments_num;

                    $installment->high_installment_price = round($installments_price);
                    $installment->save();
                }

                if (isset($down) && $down != null) {

                    $prepayment = $down * ($record->percent / 100);
                    $installments_num = Installment::getInstallmentNum($down, $record);
                    $wage = ($down - $prepayment) * 0.04 / 12 * $installments_num;
                    $tax = $wage * 0.09;
                    $loan = $down - $prepayment + $wage + $tax;
                    $installments_price = $loan / $installments_num;
                    $installment->down_installment_price = round($installments_price);
                    $installment->save();
                }

            }
        }
        if (isset($installment)) {
            if ($installment->installmentable_type == Category::class) {
                return redirect('/dashboard/installment/' . $installment->installmentable_id)->with(['update' => 'success']);
            } else {
                return redirect('/dashboard/installment/service/' . $installment->installmentable_id)->with(['update' => 'success']);
            }
        }
        return redirect()->back();
    }

    /**
     * set service installment detail setItems
     * @param $installment
     * @param Service $service
     * @return array
     */
    private function serviceInstallmentDetailSetItems($installment, Service $service)
    {
        $details=array();
        foreach ($installment as $item) {
            $cost =$item->down_interval;
            $prepayment_percentage = $service->percent;
            $prepayment =  $cost*($service->percent/100);
            $facilities =  $cost-$prepayment;
            $wage = (($facilities * ($service->wage_percent/100)) / 12) * $item->num;
            $valueـaddedـtax = (($wage / 100) * 9);
            $total_facility_payment = $wage + $valueـaddedـtax + $facilities;
            $installment_price = $item->down_installment_price;
            $details[]= [
                'sort_by' => $item->date,
                'id' => $item->id,
                'cost' => $cost,
                'prepaymentـpercentage' => $prepayment_percentage,
                'prepayment' => $prepayment,
                'facilities' => round($facilities),
                'wage' => round($wage),
                'valueـaddedـtax' =>  $valueـaddedـtax,
                'total_facility_payment' => round($total_facility_payment),
                'installment_price' => $installment_price,
                'installment_count' => $item->num,
            ];
        }
        return $details;
    }
}
