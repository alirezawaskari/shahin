<?php

namespace App\Http\Controllers\Admin;

use App\Http\Resources\MenuSettingCollection;
use App\Models\CategoryPermission;
use App\Models\Permission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\PermissionCollection;
use Illuminate\Support\Facades\Auth;

class MenuSettingController extends Controller
{
    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function index()
    {
        if (auth()->user()->hasPermission('menu_setting'))
        {
            return view( 'Admin.menuSetting.all');
        }
        return abort(401);
    }
    /**
     * @return \App\Http\Resources\MenuSettingCollection
     */
    public function filter()
    {
        $menuSetting = CategoryPermission::with('permission');
        return new MenuSettingCollection($menuSetting->paginate(20));
    }

    public function updateSubMenu(Request $request)
    {
        $permission = Permission::find($request->id);

        $permission->update([
            'display_name' => $request->display_name,
            'description' => $request->description,
            'link' => $request->link,
            $permission->isMenu = (int) $request->is_active,
        ]);

        return response()->json(['update' => 'success']);

    }

    /**
     * @param Permission $permission
     * @return \Illuminate\Contracts\FoundatiSettingon\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function edit($id)
    {
        if (auth()->user()->hasPermission('menu_setting_edit'))
        {
            $menuSetting = CategoryPermission::with('permission')->find($id);
            return view('Admin.menuSetting.edit' , compact(['menuSetting']));
        }
        return abort(401);
    }
    /**
     * @param Permission $permission
     * @param Request $request
     * @return RedirectResponse
     */
    public function update(Request $request, $id)
    {

        if (auth()->user()->hasPermission('menu_setting_edit'))
        {
            $categoryPermission = CategoryPermission::whereId($id)->first();
            $categoryPermission->update($request->all());
            return redirect()->back()->with([
                'update' => 'success',
            ]);
        }
        return redirect()->back()->with([
            'failByMessage' => 'امکان ویرایش برای شما وجود ندارد.',
        ]);
    }
}

