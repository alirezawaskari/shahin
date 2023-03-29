<?php

namespace App\Http\Controllers\Admin;

use App\Imports\PhoneImport;
use App\Models\PhoneList;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;

class PhoneListController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return void
     */
    public function index()
    {
        $phonelists = PhoneList::latest()->get();
        return view('Admin.phonelist.all' , compact('phonelists'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('Admin.phonelist.create');
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        $this->validate($request , [
           'title' => 'required',
           'file' => 'required|mimes:xlsx',
        ]);

        $item = PhoneList::create([
           'title' => $request->input('title')
        ]);


        Excel::import(new PhoneImport($item->id), request()->file('file'));

        return redirect(route('phonelist.index'))->with([
            'phonelist_create' => 'success'
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\PhoneList  $phoneList
     * @return \Illuminate\Http\Response
     */
    public function show(PhoneList $phoneList)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\PhoneList  $phoneList
     * @return \Illuminate\Http\Response
     */
    public function edit(PhoneList $phoneList)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\PhoneList  $phoneList
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, PhoneList $phoneList)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\PhoneList $phoneList
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
    public function destroy(PhoneList $phoneList)
    {
        $phoneList->delete();

        return redirect(route('phonelist.index'))->with([
            'phonelist_delete' => 'success'
        ]);
    }
}
