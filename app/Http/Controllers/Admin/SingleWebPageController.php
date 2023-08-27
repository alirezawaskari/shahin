<?php

namespace App\Http\Controllers\Admin;

use App\Models\SingleWebPage;
use App\Models\Service;
use App\Models\Province;
use Illuminate\Http\Request;
use App\Helpers\Helper;

class SingleWebPageController extends AdminController
{
    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function index()
    {
        $single_web_pages = SingleWebPage::latest()->get();
        return view('Admin.singleWebPage.all', compact('single_web_pages'));
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function create()
    {
        $services = Service::where('status' , true)->get();
        return view('Admin.singleWebPage.create' , compact('services'));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        $this->validate(request(), [
            'fa_name' => 'required|string',
            'en_name' => 'required|string',
            'status' => 'required|string',
            'service_id' => 'required',
        ]);
        $inputs = $request->all();
        $inputs['agent_id'] = auth()->user()->id;
        if($request->urlimage)
        {
            $inputs['image'] = Helper::moveImage($request->urlimage,'document');
        }

        unset($inputs['service_id']);
        unset($inputs['urlimage']);

        $single_web_page = SingleWebPage::create($inputs);

        $single_web_page->services()->sync($request->input('service_id'));

        return redirect(route('singleWebPage.index'))->with([
            'create' => 'success'
        ]);
    }

    /**
     * @param Representation $representation
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function edit(SingleWebPage $singleWebPage)
    {
        $services = Service::all();
        $provinces = Province::all();
        return view('Admin.singleWebPage.edit' ,compact('singleWebPage' , 'services' , 'provinces'));
    }

    /**
     * @param Request $request
     * @param Representation $representation
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request, SingleWebPage $singleWebPage)
    {
        $this->validate(request(), [
            'fa_name' => 'required|string',
            'slug' => 'required|string',
            'status' => 'required|string',
            'service_id' => 'required',
        ]);
        $inputs = $request->all();

        if($request->urlimage)
        {
            Helper::deleteImage($singleWebPage->image);
            $inputs['image'] = Helper::moveImage($request->urlimage,'document');
        }

        unset($inputs['service_id']);
        unset($inputs['urlimage']);

        $singleWebPage->update($inputs);
        $singleWebPage->services()->sync($request->input('service_id'));

        return redirect(route('singleWebPage.index'))->with([
            'update' => 'success'
        ]);
    }

    /**
     * @param Representation $representation
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function destroy(SingleWebPage $single_web_page)
    {
        $single_web_page->delete();
        return redirect(route('singleWebPage.index'))->with([
            'delete' => 'success'
        ]);
    }
}
