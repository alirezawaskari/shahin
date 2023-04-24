<?php

namespace App\Http\Controllers\Admin;

use App\Exports\PhonesExport;
use App\Http\Controllers\Controller;
use App\Models\Phone;
use Illuminate\Http\Request;

class PhoneController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $phones = Phone::where('phone_list_id' , 0)->latest()->paginate(20);
        $count = Phone::count();
        return view('Admin.phone.all' , compact('phones' , 'count'));
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
     * @param  \App\Phone  $phone
     * @return \Illuminate\Http\Response
     */
    public function show(Phone $phone)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Phone  $phone
     * @return \Illuminate\Http\Response
     */
    public function edit(Phone $phone)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Phone  $phone
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Phone $phone)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Phone  $phone
     * @return \Illuminate\Http\Response
     */
    public function destroy(Phone $phone)
    {
        $phone->delete();
        return redirect()->back()->with([
            'delete' => 'success'
        ]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application|\Illuminate\Http\Response|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function phoneFilter(Request $request)
	{
		if($request->type == "all")
		{
			$phones = Phone::where('phone_list_id' , 0)->latest()->paginate(20);
            $count = Phone::count();
		}
		else
		{
		$phones = Phone::where('phone_list_id' , 0)->whereBetween('created_at', [$request->startTime, $request->endTime])
			->latest()->paginate(20);
        $count = Phone::count();
		}
		if ($request->exel == 'on') {
		return (new PhonesExport(request('type') , request('startTime') , request('endTime')))->download('phones.xlsx');
        }
        return view('Admin.phone.all' , compact('phones' , 'count'));
	}

    /**
     * @param Request $request
     * @return \Illuminate\Http\Response|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function filter(Request $request)
    {
		return (new PhonesExport(request('type') , request('start_time') , request('final_time')))->download('phones.xlsx');
    }

    /**
     * @return \Illuminate\Http\Response|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function exel()
    {
        $phones = Phone::latest()->get();
        return (new PhonesExport($phones))->download('phones.xlsx');
    }
}
