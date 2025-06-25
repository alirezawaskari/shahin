<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GatewayChoose;
use Illuminate\Http\Request;

class GatewayChooseController extends Controller
{
    public function index(Request $request)
    {
        $query = GatewayChoose::query();

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $gateways = $query->orderBy('id', 'desc')->get();

        return view('Admin.gatewayChoose.index', [
            'gateways' => $gateways,
            'title' => 'مدیریت درگاه‌ها'
        ]);
    }


    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'status' => 'required|boolean'
        ]);

        GatewayChoose::create($request->only('name', 'status'));
        alert()->success('موفق','درگاه با موفقیت اضافه شد.');
        return redirect()->back();
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'status' => 'required|boolean'
        ]);

        $gateway = GatewayChoose::findOrFail($id);
        $gateway->update($request->only('name', 'status'));

        alert()->success('موفق','درگاه با موفقیت به روز شد');
        return redirect()->back();
    }

    public function destroy($id)
    {
        $gateway = GatewayChoose::findOrFail($id);
        $gateway->delete();

        alert()->success('موفق', 'درگاه با موفقیت حذف شد');
        return redirect()->back();
    }
}
