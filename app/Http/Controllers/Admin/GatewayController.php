<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\Helper;
use App\Helpers\MediaLibraryHelper;
use App\Http\Controllers\Controller;
use App\Models\Gateway;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class GatewayController extends Controller
{
    public function index(Request $request)
    {
        $query = Gateway::query();
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        $gateways = $query->orderBy('id', 'desc')->get();
        return view('Admin.gateway.index', [
            'gateways' => $gateways,
            'title' => 'مدیریت درگاه‌ها'
        ]);
    }

    public function create()
    {
        return view('Admin.gateway.create',[
            'title' => 'ایجاد درگاه'
        ]);
    }
    public function store(Request $request)
    {
        $request->validate([
            'fa_name' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'logo' => ['required', 'mimes:jpeg,jpg,png', 'max:20000'],
            'status' => ['required', 'boolean']
        ]);
        $create = $request->only([
            'name',
            'fa_name',
            'status'
        ]);
        if ($request->logo)
            $create['logo'] = MediaLibraryHelper::uploadImage($request->logo, 'gateway');
        Gateway::create($create);
        alert()->success('موفق', 'درگاه با موفقیت اضافه شد.');
        return redirect(route('gateway.index') );
    }
    public function edit(Gateway $gateway)
    {
        return view('Admin.gateway.edit',[
            'gateway' => $gateway,
            'title' => 'ایجاد درگاه'
        ]);
    }
    public function update(Request $request, Gateway $gateway)
    {
        $request->validate([
            'fa_name' => ['nullable', 'string', 'max:255'],
            'name' => ['nullable', 'string', 'max:255'],
            'logo' => ['nullable', 'mimes:jpeg,jpg,png', 'max:20000'],
            'status' => ['nullable', 'boolean']
        ]);
        $update = $request->only([
            'name',
            'fa_name',
            'status'
        ]);
        if ($request->logo){
            if(File::exists(public_path($gateway->logo)))
                File::delete(public_path($gateway->logo));
            $update['logo'] = MediaLibraryHelper::uploadImage($request->logo, 'gateway');
        }
        $gateway->update($update);
        alert()->success('موفق', 'درگاه با موفقیت به روز شد');
        return redirect(route('gateway.index') );
    }

    public function destroy(Gateway $gateway)
    {
        $gateway->delete();
        alert()->success('موفق', 'درگاه با موفقیت حذف شد');
        return redirect()->back();
    }
}
