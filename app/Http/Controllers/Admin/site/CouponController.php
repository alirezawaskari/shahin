<?php

namespace App\Http\Controllers\Admin\site;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Service;
use Hekmatinasser\Verta\Verta;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $coupons = Coupon::with('couponItems')
//            ->whereHas('couponItems', function ($query)  {
//            $query->where('center_id',null);
//        })
            ->orderBy('id','DESC')->paginate(20);
        return view('Admin.site.coupon.lists', compact('coupons'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $services = Service::filter()->get();
        return view('Admin.site.coupon.create', compact('services'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'title' => 'required',
            'code' => 'required',
            'discount' => 'required|numeric',
            'capacity' => 'required|numeric',
            'start_at' => 'required',
            'end_at' => 'required',
        ],
            [
                'title.required' => 'لطفا عنوان را وارد نمایید',
                'code.required' => 'لطفا کد تخفیف را وارد نمایید',
                'discount.required' => 'لطفا درصد تخفیف را وارد نمایید',
                'capacity.required' => 'لطفا ظرفیت را وارد نمایید',
                'start_at.required' => 'لطفا تاریخ آغاز را وارد نمایید',
                'end_at.required' => 'لطفا تاریخ پایان را وارد نمایید',
            ]);


        if(isset($request['purchase_type']) && $request['purchase_type'] == 1){
            if(isset($request['presence_pay_type_zero']) && $request['presence_pay_type_zero'] == 0){
                $request['pay_type'] = 0 ;
            }
            if(isset($request['presence_pay_type']) && $request['presence_pay_type'] == 1){
                $request['pay_type'] = 1 ;
            }
            if(isset($request['presence_pay_type_zero']) && $request['presence_pay_type_zero'] == 0 &&
                isset($request['presence_pay_type']) && $request['presence_pay_type'] == 1){
                $request['pay_type'] = 2 ;
            }
        }

        if(isset($request['purchase_type']) && $request['purchase_type'] == 0){
            if(isset($request['online_pay_type_zero']) && $request['online_pay_type_zero'] == 0){
                $request['pay_type'] = 0 ;
            }
            if(isset($request['online_pay_type']) && $request['online_pay_type'] == 1){
                $request['pay_type'] = 1 ;
            }
            if(isset($request['online_pay_type_zero']) && $request['online_pay_type_zero'] == 0 &&
                isset($request['online_pay_type']) && $request['online_pay_type'] == 1){
                $request['pay_type'] = 2 ;
            }
        }

        $start_at = Helper::convert($request->start_at);
        $end_at = Helper::convert($request->end_at);
        $v_start_at = Verta::parse( $start_at );
        $v_end_at = Verta::parse( $end_at );

        $request['start_at'] = $v_start_at->formatGregorian('Y-m-d');
        $request['end_at'] = $v_end_at->formatGregorian('Y-m-d');

        $inputs = $request->all();
        Coupon::create(array_merge($inputs));

        return redirect(route('coupon.index'))->with([
            'create' => 'success'
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Coupon $coupon
     * @return \Illuminate\Http\Response
     */
    public function show(Coupon $coupon)
    {
        return view('coupon.edit', compact('coupon'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Coupon $coupon
     * @return \Illuminate\Http\Response
     */
    public function edit(Coupon $coupon)
    {
        $services = Service::filter()->get();
        return view('Admin.site.coupon.edit', compact('coupon' , 'services'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \App\Models\Coupon $coupon
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request,$id)
    {
        $this->validate($request, [
            'title' => 'required',
            'code' => 'required',
            'discount' => 'required|numeric',
            'capacity' => 'required|numeric',
            'start_at' => 'required',
            'end_at' => 'required',
        ],
            [
                'title.required' => 'لطفا عنوان را وارد نمایید',
                'code.required' => 'لطفا کد تخفیف را وارد نمایید',
                'discount.required' => 'لطفا درصد تخفیف را وارد نمایید',
                'capacity.required' => 'لطفا ظرفیت را وارد نمایید',
                'start_at.required' => 'لطفا تاریخ آغاز را وارد نمایید',
                'end_at.required' => 'لطفا تاریخ پایان را وارد نمایید',
            ]);

        if(isset($request['purchase_type']) && $request['purchase_type'] == 1){
            if(isset($request['presence_pay_type_zero']) && $request['presence_pay_type_zero'] == 0){
                $request['pay_type'] = 0 ;
            }
            if(isset($request['presence_pay_type']) && $request['presence_pay_type'] == 1){
                $request['pay_type'] = 1 ;
            }
            if(isset($request['presence_pay_type_zero']) && $request['presence_pay_type_zero'] == 0 &&
                isset($request['presence_pay_type']) && $request['presence_pay_type'] == 1){
                $request['pay_type'] = 2 ;
            }
        }

        if(isset($request['purchase_type']) && $request['purchase_type'] == 0){
            if(isset($request['online_pay_type_zero']) && $request['online_pay_type_zero'] == 0){
                $request['pay_type'] = 0 ;
            }
            if(isset($request['online_pay_type']) && $request['online_pay_type'] == 1){
                $request['pay_type'] = 1 ;
            }
            if(isset($request['online_pay_type_zero']) && $request['online_pay_type_zero'] == 0 &&
                isset($request['online_pay_type']) && $request['online_pay_type'] == 1){
                $request['pay_type'] = 2 ;
            }
        }

        $start_at = Helper::convert($request->start_at); $end_at = Helper::convert($request->end_at);

        $v_start_at = Verta::parse( $start_at ); $v_end_at = Verta::parse( $end_at );

        $request['start_at'] = $v_start_at->formatGregorian('Y-m-d');

        $request['end_at'] = $v_end_at->formatGregorian('Y-m-d');

        $inputs = $request->all();
        $coupon=Coupon::find($id);
        $coupon->update($inputs);

        return redirect(route('coupon.index'))->with([
            'update' => 'success'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Coupon $coupon
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
    public function destroy(Coupon $coupon)
    {
        $coupon->delete();
        return redirect(route('coupon.index'))->with([
            'coupon_delete' => 'success'
        ]);
    }


    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function discountVarietyProduct()
    {
        $coupons = Coupon::with('couponItems')->whereHas('couponItems', function ($query)  {
            $query->where('center_id','!=',null);
        })->orderBy('id','DESC')->paginate(25);

        return view('Admin.site.coupon.lists', compact('coupons'));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Foundation\Application|\Illuminate\Http\Response
     */
    public function statusCoupon(Request $request)
    {
        if(isset($request->id)){
            $coupon = Coupon::find($request->id);
            $coupon->status = $request->status;
            $coupon->save();
            return response(['status'=>'true']);
        }
        return response(['status'=>'false']);
    }
}
