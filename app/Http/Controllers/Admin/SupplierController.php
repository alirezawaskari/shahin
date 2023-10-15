<?php

namespace App\Http\Controllers\Admin;

use App\Models\Supplier;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class SupplierController extends Controller
{
    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function index()
    {
        $suppliers = Supplier::all();
        return view('Admin.supplier.all', compact('suppliers'));
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function create()
    {
        $users = self::getSupplierUsers();
        return view('Admin.supplier.create' , compact('users'));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     * @throws \Illuminate\Validation\ValidationException
     */
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

    /**
     * @param Supplier $supplier
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function edit(Supplier $supplier)
    {
        $users = self::getSupplierUsers();
        return view('Admin.supplier.edit', compact(['supplier' , 'users']));
    }

    /**
     * @param Request $request
     * @param Supplier $supplier
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     * @throws \Illuminate\Validation\ValidationException
     */
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
    private function getSupplierUsers()
    {
        $users = User::where('level' , 'admin')->whereHas('roles', function ($query) {
            $query->where('name' , 'supplier');
        })->get();
        
        return $users;
    }
}
