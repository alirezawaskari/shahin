<?php

namespace App\Http\Controllers\Admin;

use App\Models\AgencySite;
use App\Models\AgencySiteTemplate;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;

class AgencyManageController extends Controller
{
    # create
    public function create ()
    {
        $data['agency_site_templates'] = AgencySiteTemplate::get();

        return view('admin.agency-manage.create', compact('data'));
    }

    # store
    public function store (Request $request)
    {
        $this->validate($request, [
            'title' => ['required'],
            'subdomain' => ['required', Rule::unique('agency_sites', 'subdomain')],
            'agency_site_template_id' => ['required']
        ], [], [
            'title' => 'عنوان',
            'subdomain' => 'ساب دامین',
            'agency_site_template_id' => 'قالب'
        ]);

        $agencySite = new AgencySite();
        $agencySite->agency_site_template_id = $request->input('agency_site_template_id');
        $agencySite->title = $request->input('title');
        $agencySite->subdomain = $request->input('subdomain');
        $agencySite->save();

        return redirect()->back();
    }
}
