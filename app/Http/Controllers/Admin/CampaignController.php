<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\CampaignCollaboration;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CampaignController extends Controller
{
    public function index()
    {
        $title = "کمپین";
        $campaigns = Campaign::all();
        return view('Admin.campaign.index',compact('campaigns','title'));
    }
    public function create()
    {
        return view('Admin.campaign.create');
    }
    public function store(Request $request)
    {
        if($request->url_profile_image){
            $image = Helper::moveImage($request->url_profile_image,'campaign');
        }
        if ($request->url_header_image){
            $image_header = Helper::moveImage($request->url_header_image,'campaign');
        }
        Campaign::create([
            'name' => $request->name,
            'subject' => $request->subject,
            'keyword' => $request->keyword,
            'capacity' => $request->capacity,
            'content' => $request->content,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'image' => $image ?? null,
            'image_header' =>  $image_header?? null,
        ]);
        alert()->success('', 'عملیات موفق');
        return redirect(route('campaign.index'));
    }
    public function edit(Campaign $campaign)
    {
        return view('Admin.campaign.edit',compact('campaign'));
    }
    public function createCompany(Request $request)
    {
//        dd();
//        foreach ($request->)
//        CampaignCollaboration::create();
    }
}
