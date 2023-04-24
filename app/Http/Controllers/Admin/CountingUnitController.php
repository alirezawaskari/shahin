<?php

namespace App\Http\Controllers\Admin;

use App\Models\CountingUnit;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;

class CountingUnitController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $counting_units = CountingUnit::latest()->get();
        return view('Admin.countingunit.all', compact('counting_units'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('Admin.countingunit.create');
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
        $this->validate(request(), [
            'title' => 'required|string',
        ]);
        $counting_unit = CountingUnit::create($request->all());
        //cache
        Cache::flush();
        return redirect(route('countingUnit.index'))->with([
            'create' => 'success',
        ]);
    }

    /**
     * @param CountingUnit $countingUnit
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function edit(CountingUnit $countingUnit)
    {
        return view('Admin.countingunit.edit' , compact('countingUnit'));
    }

    /**
     * @param Request $request
     * @param CountingUnit $countingUnit
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request, CountingUnit $countingUnit)
    {
        $this->validate(request(), [
            'title' => 'required|string',
        ]);

        $inputs = $request->all();
        $countingUnit->update($inputs);

        return redirect(route('countingUnit.index'))->with([
            'update' => 'success',
        ]);
    }

    /**
     * @param Role $role
     */
    public function destroy(Role $role)
    {
        //
    }
}
