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

    public function edit(CountingUnit $countingUnit)
    {
        return view('Admin.countingunit.edit' , compact('countingUnit'));
    }

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

    public function destroy(Role $role)
    {
        //
    }
}
