<?php

namespace App\Http\Controllers\Admin;


use App\Models\CategoryPermission;
use App\Models\Permission;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Validation\Rule;

class PermissionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!auth()->user()->isAbleTo('permissions')) {
            abort(403);
        }
        $categories = CategoryPermission::get();
        return view('Admin.permission.all', compact('categories'));

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (!auth()->user()->isAbleTo('permissions')) {
            abort(403);
        }
            $permission_categories = CategoryPermission::get();
            return view('Admin.permission.create', compact('permission_categories'));

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (!auth()->user()->isAbleTo('permissions')) {
            abort(403);
        }
        $request->validate([
            'name'=>['required'],
            'display_name'=>['required'],
            'description'=>['required'],
            'category_id'=>['required',Rule::exists('category_permissions','id')],
        ]);
        $per = new Permission();
        $per->name = $request->input('name');
        $per->display_name = $request->input('display_name');
        $per->description = $request->input('description');
        $per->cat_id = $request->input('category_id');
        $per->save();

        return redirect(route('permission.index'));
    }



    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Permission $permission
     * @return \Illuminate\Http\Response
     */
    public function edit(Permission $permission)
    {
        if (!auth()->user()->isAbleTo('permissions')) {
            abort(403);
        }
        $permission_categories=CategoryPermission::get();
        return view('Admin.permission.update',compact('permission','permission_categories'));
    }

    /**
     * update permission
     * @param Request $request
     * @param Permission $permission
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Permission $permission)
    {
        if (!auth()->user()->isAbleTo('permissions')) {
            abort(403);
        }
        $request->validate([
            'display_name'=>['required'],
            'description'=>['required'],
            'category_id'=>['required',Rule::exists('category_permissions','id')],
        ]);
        $permission->update([
            'display_name'=>$request->display_name,
            'description'=>$request->display_name,
            'cat_id'=>$request->category_id,
        ]);
        alert()->success('ویرایش دسترسی با موفقیت انجام شد .', 'عملیات موفق !');
        return redirect()->back();
    }
}
