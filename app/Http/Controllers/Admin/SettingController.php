<?php

namespace App\Http\Controllers\Admin;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SettingController extends AdminController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $setting = Setting::first();
        return view('Admin.setting.edit' , compact('setting'));
    }


    public function updateCompanyInfo(Request $request)
    {
        Cache::forget('setting');
        Setting::first()->update([
           'address'=>$request->company_address,
           'call'=>$request->company_call_phone,
           'email'=>$request->company_email,
            'shaba_number'=>$request->company_shaba_number
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
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \App\Models\Setting $setting
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request, Setting $setting)
    {
        $this->validate(request(), [
            'title' => 'required',
            'index_description' => 'required',
        ]);

        $inputs = $request->all();
        $logo = $request->file('logo');
        $img_box_1 = $request->file('img_box_1');
        $img_box_2 = $request->file('img_box_2');
        $fav = $request->file('fav');
        $footer_logo = $request->file('footer_logo');

        if ($img_box_1){
            $url = $this->upload($img_box_1);
            $setting->update(['img_box_1' => $url]);
        }
        if ($img_box_2){
            $url = $this->upload($img_box_2);
            $setting->update(['img_box_2' => $url]);
        }
        if ($logo){
            $logourl = $this->upload($logo);
            $setting->update(['logo' => $logourl]);
        }
        if ($fav){
            $favurl = $this->upload($fav);
            $setting->update(['fav' => $favurl]);
        }
        if ($footer_logo){
            $footerlogourl = $this->upload($footer_logo);
            $setting->update(['footer_logo' => $footerlogourl]);
        }

        unset($inputs['logo']);
        unset($inputs['fav']);
        unset($inputs['footer_logo']);
        unset($inputs['img_box_1']);
        unset($inputs['img_box_2']);
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
