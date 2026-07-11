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
            'urllogo' => ['required'],
            'status' => ['required', 'boolean']
        ], [
            'urllogo.required' => 'لطفا لوگو را بارگذاری کنید',
        ]);
        $create = $request->only([
            'name',
            'fa_name',
            'status'
        ]);
        $gateway = Gateway::create($create);
        MediaLibraryHelper::attachTo($gateway, $request->urllogo, 'logo');
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
            'urllogo' => ['nullable'],
            'status' => ['nullable', 'boolean']
        ]);
        $update = $request->only([
            'name',
            'fa_name',
            'status'
        ]);
        $gateway->update($update);
        MediaLibraryHelper::attachTo($gateway, $request->urllogo, 'logo');
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
