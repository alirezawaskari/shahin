<?php

namespace App\Http\Controllers\Admin;

use App\Models\Coupon;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CouponController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $coupons = Coupon::latest()->get();
        return view('Admin.coupon.all', compact('coupons'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $services = Service::get();
        return view('Admin.coupon.create', compact('services'));
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
        ]);

        $inputs = $request->all();
        unset($inputs['date']);
        unset($inputs['date2']);
        Coupon::create(array_merge($inputs, ['start_at' => Carbon::createFromTimestamp($inputs['start_at'] / 1000)->toDateString(), 'end_at' => Carbon::createFromTimestamp($inputs['end_at'] / 1000)->toDateString()]));

        return redirect(route('coupon.index'))->with([
            'coupon_create' => 'success'
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Coupon $coupon
     * @return \Illuminate\Http\Response
     */
    public function show(Coupon $coupon)
    {
        return view('coupon.edit', compact('coupon'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Coupon $coupon
     * @return \Illuminate\Http\Response
     */
    public function edit(Coupon $coupon)
    {
        $services = Service::all();
        return view('Admin.coupon.edit', compact('coupon' , 'services'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \App\Coupon $coupon
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request, Coupon $coupon)
    {
        $this->validate($request, [
            'title' => 'required',
            'code' => 'required',
            'discount' => 'required|numeric',
            'capacity' => 'required|numeric',
        ]);

        $inputs = $request->all();
        unset($inputs['date']);
        unset($inputs['date2']);
        unset($inputs['start_at']);
        unset($inputs['end_at']);

        if (strpos($request->input('start_at'), '-')) {
            $coupon->update($inputs);
        } else {
            $coupon->update(array_merge($inputs, ['start_at' => Carbon::createFromTimestamp($request->input('start_at') / 1000)->toDateString()]));
        }

        if (strpos($request->input('end_at'), '-')) {
            $coupon->update($inputs);
        } else {
            $coupon->update(array_merge($inputs, ['end_at' => Carbon::createFromTimestamp($request->input('end_at')/ 1000)->toDateString()]));
        }

        return redirect(route('coupon.index'))->with([
            'coupon_update' => 'success'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Coupon $coupon
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

}
