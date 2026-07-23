<?php

namespace App\Http\Controllers\Admin;

use App\Models\SingleWebPage;
use App\Models\Service;
use App\Models\Province;
use Illuminate\Http\Request;
use App\Helpers\Helper;
use App\Helpers\MediaLibraryHelper;

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
        $services = Service::filter()->get();
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
        ], [
            'fa_name.required' => 'لطفا عنوان فارسی را وارد کنید',
            'en_name.required' => 'لطفا نشان اینترنتی را وارد کنید',
            'status.required' => 'لطفا وضعیت را انتخاب کنید',
            'service_id.required' => 'لطفا حداقل یک خدمت را انتخاب کنید',
        ]);
        $inputs = $request->all();
        $inputs['agent_id'] = auth()->user()->id;

        unset($inputs['service_id']);
        // `image` too, not just urlimage: name="image" is a real file input, so a failed
        // ajax upload leaves the raw UploadedFile in $request->all() and it would be
        // written straight into the legacy `image` column. Spatie is the source of truth.
        unset($inputs['urlimage'], $inputs['image']);

        $single_web_page = SingleWebPage::create($inputs);
        MediaLibraryHelper::attachTo($single_web_page, $request->urlimage, 'image');

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
        $services = Service::filter()->get();
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
        ], [
            'fa_name.required' => 'لطفا عنوان فارسی را وارد کنید',
            'slug.required' => 'لطفا نشان اینترنتی را وارد کنید',
            'status.required' => 'لطفا وضعیت را انتخاب کنید',
            'service_id.required' => 'لطفا حداقل یک خدمت را انتخاب کنید',
        ]);
        $inputs = $request->all();

        unset($inputs['service_id']);
        unset($inputs['urlimage'], $inputs['image']); // see store()

        $singleWebPage->update($inputs);
        MediaLibraryHelper::attachTo($singleWebPage, $request->urlimage, 'image');
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
