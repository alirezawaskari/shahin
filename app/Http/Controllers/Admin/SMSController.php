<?php

namespace App\Http\Controllers\Admin;

use App\Classes\External\IranSms;
use App\Models\sms;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SMSController extends Controller
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
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\sms  $sms
     * @return \Illuminate\Http\Response
     */
    public function show(sms $sms)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\sms  $sms
     * @return \Illuminate\Http\Response
     */
    public function edit(sms $sms)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\sms  $sms
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, sms $sms)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\sms  $sms
     * @return \Illuminate\Http\Response
     */
    public function destroy(sms $sms)
    {
        //
    }

    /**
     * fetch sms credit
     */
    public function fetchSmsCredit()
    {
        $iran_sms_credit =0;
        $iran_sms_credit_error=false;
        try {
            $iran_sms_credit=IranSms::fetchCredit()->credit;
        }catch (\Exception $e){
            $iran_sms_credit_error=true;
        }
        return response([ 'data' => ['iran_sms_credit'=>$iran_sms_credit,'iran_sms_credit_error' => $iran_sms_credit_error]]);
    }
        /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response|\Illuminate\Contracts\Routing\ResponseFactory|void
     * use in negin sms postal for get sms
     */
    public function gettingIncomingSms(Request $request)
    {
        if($request->portalcode == config('sesvices.neginSms.portalcode'))
        {
            sms::create([
                'recipient_number' => $request->smsNumber,
                'sender_number' => $request->from,
                'body' => trim($request->Body),
                'date' => now(),
                'status' => 0,
            ]);
            return response([
                'operator' => true,
                'status'=>'success',
                'messages'=>'پیامک با موفقیت ثبت شد .'
            ]);
        }
        return response([
            'operator' => false,
            'status'=>'error',
            'messages'=>'شما دسترسی ندارید .'
        ]);
    }
}
