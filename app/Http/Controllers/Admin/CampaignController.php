<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\BeforeForm;
use App\Models\Campaign;
use App\Models\CampaignCollaboration;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

class CampaignController extends Controller
{
    public function index()
    {
        $title = "پوییش";
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
            'usage' => $request->capacity,
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
        $data_display_levels=BeforeForm::getDataDisplayLevel();
        $level_save_data=BeforeForm::getLevelSaveData();
        return view('Admin.campaign.edit',compact('campaign','data_display_levels','level_save_data'));
    }
    public function update(Request $request,$campaign)
    {
        $campaign = Campaign::find($campaign);
        if($request->url_profile_image){
            if (File::exists(public_path($campaign->image))) {
                    File::delete(public_path($campaign->image));
            }
            $image = Helper::moveImage($request->url_profile_image,'campaign');
            $campaign->image = $image;
        }
        if($request->url_header_image){
            if (File::exists(public_path($campaign->image_header))) {
                    File::delete(public_path($campaign->image_header));
            }
            $image_header = Helper::moveImage($request->url_header_image,'campaign');
            $campaign->image_header = $image_header;
        }
        $campaign->update([
            'name' => $request->name,
            'subject' => $request->subject,
            'keyword' => $request->keyword,
            'capacity' => $request->capacity,
            'usage' => $request->capacity,
            'content' => $request->content,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
        ]);
        alert()->success('', 'عملیات موفق');
        return redirect(route('campaign.index'));
    }
    public function createCompany(Request $request,$campaign_id)
    {
        $names = $request->name_company;
        $urls = $request->url_company;
        $logos = $request->logo_company_url;
        foreach ($names as $i => $name) {
            CampaignCollaboration::create([
                'campaign_id'     => $campaign_id,
                'name'     => $name,
                'url'      => $urls[$i],
                'logo' => Helper::moveImage($logos[$i], 'company'),
            ]);
        }
        return redirect(route('campaign.index'));
    }
    public function deleteCompany($campaign_id)
    {
        CampaignCollaboration::findOrFail($campaign_id)->delete();
        return redirect()->back();
    }
    public function createForm(Request $request)
    {
        dd($request);
    }
}
