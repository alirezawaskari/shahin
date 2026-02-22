<?php

namespace App\Http\Controllers\Admin;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SettingController extends AdminController
{
    /**
     * Summary of index
     * @return \Illuminate\Contracts\View\View
     */
    public function index()
    {
        $setting = Setting::filter()->first();
        return view('Admin.setting.edit' , compact('setting'));
    }


    public function updateCompanyInfo(Request $request)
    {
        Cache::forget('setting');
        Setting::filter()->first()->update([
           'address'=>$request->company_address,
           'call'=>$request->company_call_phone,
           'email'=>$request->company_email,
           'shaba_number'=>$request->company_shaba_number,
           'card_number'=>$request->company_card_number,
           'card_holder'=>$request->company_card_holder,
           'tax_free_shaba_number'=>$request->company_tax_free_shaba_number,
           'tax_free_card_number'=>$request->company_tax_free_card_number,
           'tax_free_card_holder'=>$request->company_tax_free_card_holder,
        ]);
        alert()->success('','عملیات موفق');
        return redirect()->back();
    }
    public function updateSmsSetting(Request $request)
    {
        Cache::forget('setting');
        Setting::first()->update([
            'sms_panel' => $request->sms_panel
        ]);
        alert()->success('','عملیات موفق');
        return redirect()->back();
    }

    /**
     * Summary of update
     * @param Request $request
     * @param Setting $setting
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Setting $setting)
    {
        $this->validate(request(), [
            'title' => 'required',
            'index_description' => 'required',
        ]);

        $inputs = $request->all();
        $setting->update($inputs);

        //cache
        Cache::flush();

        return redirect(route('dashboard'))->with([
            'setting_update'=>'success'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Setting  $setting
     * @return \Illuminate\Http\Response
     */
    public function destroy(Setting $setting)
    {
        //
    }
}
