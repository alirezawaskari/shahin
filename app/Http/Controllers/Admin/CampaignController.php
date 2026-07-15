<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\Helper;
use App\Helpers\MediaLibraryHelper;
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
        $campaign = Campaign::create([
            'name' => $request->name,
            'subject' => $request->subject,
            'keyword' => $request->keyword,
            'capacity' => $request->capacity,
            'usage' => $request->capacity,
            'content' => $request->content,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'slug' => $request->slug,
            'form' => $request->form,
        ]);
        if ($request->url_profile_image) {
            MediaLibraryHelper::attachTo($campaign, $request->url_profile_image, 'image');
        }
        if ($request->url_header_image) {
            MediaLibraryHelper::attachTo($campaign, $request->url_header_image, 'image_header');
        }
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
        if ($request->url_profile_image) {
            MediaLibraryHelper::attachTo($campaign, $request->url_profile_image, 'image');
        }
        if ($request->url_header_image) {
            MediaLibraryHelper::attachTo($campaign, $request->url_header_image, 'image_header');
        }
        $campaign->update([
            'name' => $request->name,
            'subject' => $request->subject,
            'keyword' => $request->keyword,
            'capacity' => $request->capacity,
            'usage' => $request->capacity,
            'content' => $request->content,
            'slug' => $request->slug,
            'form' => $request->form,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
        ]);
        alert()->success('', 'عملیات موفق');
        return redirect(route('campaign.index'));
    }
    public function createCompany(Request $request,$campaign_id)
    {
        $campaign = Campaign::findOrFail($campaign_id);
        $names = $request->name_company;
        $urls = $request->url_company;
        $logos = $request->logo_company_url;
        foreach ($names as $i => $name) {
            $collaboration = CampaignCollaboration::create([
                'campaign_id'     => $campaign_id,
                'name'     => $name,
                'url'      => $urls[$i],
            ]);
            $media = MediaLibraryHelper::attachTo($campaign, (string) $logos[$i], 'company_logos');
            $collaboration->update([
                'logo' => $media ? $media->getUrl() : '',
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
