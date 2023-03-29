<?php

namespace App\Http\Controllers\Admin;

use App\Models\Reason;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ReasonController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $reasons = Reason::get();
        return view('Admin.reason.all' , compact('reasons'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('Admin.reason.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return void
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        $this->validate(request(), [
            'title' => 'required|string',
        ]);

        Reason::create($request->all());

        return redirect(route('reason.index'))->with([
            'reason_create' => 'success'
        ]);

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Reason  $reason
     * @return \Illuminate\Http\Response
     */
    public function show(Reason $reason)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Reason  $reason
     * @return \Illuminate\Http\Response
     */
    public function edit(Reason $reason)
    {
        return view('Admin.reason.edit' , compact('reason'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \App\Reason $reason
     * @return void
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request, Reason $reason)
    {
        $this->validate(request(), [
            'title' => 'required|string',
        ]);

        $reason->update($request->all());

        return redirect(route('reason.index'))->with([
            'reason_update' => 'success'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Reason $reason
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
    public function destroy(Reason $reason)
    {
        $reason->delete();

        return redirect(route('reason.index'))->with([
            'reason_delete' => 'success'
        ]);

    }
}
