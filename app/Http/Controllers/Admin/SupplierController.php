<?php

namespace App\Http\Controllers\Admin;

use App\Models\Supplier;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;

class SupplierController extends Controller
{
    public function index()
    {
        $suppliers = Supplier::all();
        return view('Admin.supplier.all', compact('suppliers'));
    }

    public function create()
    {
        return view('Admin.supplier.create');
    }

    public function store(Request $request)
    {
        $this->validate(request(), [
            'name' => 'required',
            'address' => 'required',
        ]);
        $supplier = Supplier::create($request->all());
        //cache
        Cache::flush();

        return redirect(route('supplier.index'))->with([
            'create'=>'success'
        ]);
    }

    public function show(Menu $menu)
    {
        //
    }

    public function edit(Supplier $supplier)
    {
        return view('Admin.supplier.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $this->validate(request(), [
            'name' => 'required',
            'address' => 'required',
        ]);

        $inputs = $request->all();
        $supplier->update($inputs);

        //cache
        Cache::flush();

        return redirect(route('supplier.index'))->with([
            'update' => 'success'
        ]);
    }
}
