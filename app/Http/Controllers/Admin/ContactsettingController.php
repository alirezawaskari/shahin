<?php

namespace App\Http\Controllers\Admin;

use App\Models\Contactsetting;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;

class ContactsettingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $contactsetting = Contactsetting::first();
        return view('Admin.contactsetting.edit', compact('contactsetting'));
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
     * @param  \App\Contactsetting  $contactsetting
     * @return \Illuminate\Http\Response
     */
    public function show(Contactsetting $contactsetting)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Contactsetting  $contactsetting
     * @return \Illuminate\Http\Response
     */
    public function edit(Contactsetting $contactsetting)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \App\Contactsetting $contactsetting
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request, Contactsetting $contactsetting)
    {
        $this->validate(request(), [
            'title' => 'required',
            'description' => 'required',
        ]);

        $contactsetting->update($request->all());

        //cache
        Cache::flush();

        return redirect(route('dashboard'))->with([
            'setting_update'=>'success'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Contactsetting  $contactsetting
     * @return \Illuminate\Http\Response
     */
    public function destroy(Contactsetting $contactsetting)
    {
        //
    }
}
