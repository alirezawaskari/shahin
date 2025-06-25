<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CampaignController extends Controller
{
    public function campaign()
    {
        $company_info = Setting::select('content_campaign')->first();
        return view('Admin.campaign.index',compact('company_info'));
    }
    public function updateContentCampaign(Request $request)
    {
        Cache::forget('setting');
        Setting::first()->update([
            'content_campaign' => $request['content'],
        ]);
        alert()->success('', 'عملیات موفق');
        return redirect()->back();
    }
}
