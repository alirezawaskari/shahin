<?php

namespace App\Http\Controllers\Admin;

use App\Models\BeforeForm;
use App\Models\BeforeFormItem;
use App\Models\ContractForm;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class BeforeFormItemController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\HttpResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
       $this->validate($request , [
           'title' => 'required|string',
           'value' => 'required|string',
           'beforeformitemable_id'=>'required',
           'beforeformitemable_type'=>'required|string'
       ]);

       BeforeFormItem::create([
           'title' => $request->title,
           'value' => $request->value,
           'beforeformitemable_id'=>$request->beforeformitemable_id,
           'beforeformitemable_type'=>$request->beforeformitemable_type]
       );

            alert()->success('','عملیات موفق')->cancelButton('باشه');
            return redirect()->back();
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\BeforeFormItem  $beforeFormItem
     * @return \Illuminate\Http\Response
     */
    public function edit(BeforeFormItem $beforeFormItem)
    {
       return view('Admin.beforeformitem.edit' , compact('beforeFormItem'));
    }


    /**
     * update before form item
     * @param Request $request
     * @param BeforeFormItem $beforeFormItem
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request, BeforeFormItem $beforeFormItem)
    {
        $this->validate($request , [
            'title' => 'required|string',
            'value' => 'required|string',
        ]);

        $beforeFormItem->update([
            'title'=>$request->title,
            'value'=>$request->value,
        ]);
        alert()->success(' ','عملیات موفق')->cancelButton('باشه');
        return back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\BeforeFormItem $beforeFormItem
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
    public function destroy(BeforeFormItem $beforeFormItem)
    {
        $beforeFormItem->delete();
        return redirect()->to(url()->previous())->with([
            'before_form_item_delete' => 'success'
        ]);
    }
}
