<?php

namespace App\Http\Controllers\Admin;

use App\Models\CategoryPermission;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Validation\Rule;

class CategoryPermissionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $category_permissions= CategoryPermission::latest()->get();
        return view('Admin.categoryPermission.all',compact('category_permissions'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('Admin.categoryPermission.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|min:3|unique:category_permissions,name',
        ]);
          CategoryPermission::create([
            'name'=>$request->name
        ]);
        alert()->success('دسته بندی با موفقیت ثبت گردید !', 'عملیات موفق !');
        return redirect(route('category-permission.index'));

    }



    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\CategoryPermission  $categoryPermission
     * @return \Illuminate\Http\Response
     */
    public function edit(CategoryPermission $categoryPermission)
    {
        return view('Admin.categoryPermission.edit',compact('categoryPermission'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\CategoryPermission  $categoryPermission
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, CategoryPermission $categoryPermission)
    {
        $request->validate([
            'name' => [Rule::unique('category_permissions')->ignore($categoryPermission->id),'required','min:3']
        ]);
       $categoryPermission->update([
           'name'=>$request->name
       ]);
        alert()->success('تغییرات با موفقیت ثبت گردید !', 'عملیات موفق !');
        return redirect(route('category-permission.index'));
    }

}
