<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\TariffResource;
use App\Models\Tariff;
use Illuminate\Http\Request;

class TariffController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if(request()->ajax()) {
            $tariffs = Tariff::filter()->paginate(20);
            return TariffResource::collection($tariffs);
        }
        $title = "تعرفه ها";
        return view('Admin.tariff.index',compact('title'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('Admin.tariff.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->validate(request(), [
            'title' => ['required','string'],
            'service_id' => ['required','exists:services,id'],
        ]);
        $create = $request->only([
            'title',
            'service_id'
        ]);
        Tariff::create($create);
        return redirect(route('tariff.index'))->with([
            'create' => 'success'
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Tariff $tariff)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Tariff $tariff)
    {
        return view('Admin.tariff.edit',compact('tariff'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Tariff $tariff)
    {
        $this->validate(request(), [
            'title' => ['sometimes','nullable','string'],
            'service_id' => ['sometimes','nullable','exists:services,id'],
        ]);
        $update = $request->only([
            'title',
            'service_id'
        ]);
        $tariff->update($update);
        return redirect(route('tariff.index'))->with([
            'update' => 'success'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Tariff $tariff)
    {
        $tariff->delete();
        return redirect()->back()->with([
            'status' => 'success'
        ]);

    }
}
