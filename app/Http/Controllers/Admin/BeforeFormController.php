<?php

namespace App\Http\Controllers\Admin;

use App\Models\BeforeForm;
use App\Models\Service;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class BeforeFormController extends Controller
{

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {

        $this->validate($request , [
           'placeholder' => 'required|string',
           'name' => 'nullable|string',
           'value' => 'nullable|string',
           'help' => 'nullable|string',
           'class' => 'nullable|string',
           'required' => 'required|numeric',
           'type' => 'required|string',
           'data_display_level' => 'required',
           'data_save_level' => 'required',
           'beforeformable_id' => 'required',
           'beforeformable_type' => 'required',
        ]);
//            return $request->all();
        BeforeForm::create($request->all());
        alert()->success('','عملیات موفق');
        if ($request->beforeformable_type == Service::class){
            return redirect()->to('/dashboard/service_before_form/' . Service::find($request->beforeformable_id)->slug)->with([
                'before_form_create' => 'success'
            ]);
        }else{
            exit('TODO:develop for new model');
        }
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\BeforeForm  $beforeForm
     * @return \Illuminate\Http\Response
     */
    public function edit(BeforeForm $beforeForm)
    {
        $level_save_data=BeforeForm::getLevelSaveData();
        $data_display_levels=BeforeForm::getDataDisplayLevel();
        return view('Admin.beforeform.edit' , compact('beforeForm','level_save_data','data_display_levels'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\BeforeForm $beforeForm
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request, BeforeForm $beforeForm)
    {
        $this->validate($request , [
            'placeholder' => 'nullable|string',
            'name' => 'nullable|string',
            'value' => 'nullable|string',
            'help' => 'nullable|string',
            'class' => 'nullable|string',
            'required' => 'required|numeric',
            'type' => 'required|string',
        ]);

        $beforeForm->update($request->all());
        return redirect()->to('/dashboard/service_before_form/' . Service::find($request->input('beforeformable_id'))->slug)->with([
            'before_form_create' => 'success'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\BeforeForm $beforeForm
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
    public function destroy(BeforeForm $beforeForm)
    {
        $beforeForm->beforeformitems()->delete();
        $beforeForm->delete();
        return redirect()->to(url()->previous())->with([
            'before_form_delete' => 'success'
        ]);
    }


}
