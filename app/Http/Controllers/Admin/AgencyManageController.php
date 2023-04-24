<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AgencySite;
use App\Models\AgencySiteTemplate;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;


class AgencyManageController extends Controller
{
    # create
    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function create ()
    {
        $data['agency_site_templates'] = AgencySiteTemplate::get();

        return view('admin.agency-manage.create', compact('data'));
    }

    # store

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Validation\ValidationException
     */
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
