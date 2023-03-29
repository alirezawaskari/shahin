<?php

namespace App\Http\Controllers\Admin\ContextTraits;

use App\Models\Context;
use Illuminate\Http\Request;

trait SiteRules
{
    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Foundation\Application|\Illuminate\View\View
     */
    public function fetchSiteRules()
    {
         $site_rules = array('body' => Context::whereIn('key', Context::SITE_RULES_KEYS)->first()->value);
        return view('Admin.siteRules.index', compact('site_rules'));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Exception
     */
    public function updateSiteRules(Request $request)
    {
        if (!auth()->user()->hasPermission('update_site_rules')) {
            abort(403);
        }
        Context::updateContext(array(['key'=>'site_rules_body','value'=>!is_null($request->body)?$request->body:'']));
        alert()->success('','عملیات موفق')->persistent('باشه');
        return redirect()->back();
    }
}
