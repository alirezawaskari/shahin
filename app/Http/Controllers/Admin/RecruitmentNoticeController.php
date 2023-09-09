<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\Helper;
use App\Models\Province;
use App\Models\RecruitmentNotice;
use App\Models\Representation;
use Illuminate\Http\Request;

class RecruitmentNoticeController extends AdminController
{
    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application|\never
     */
    public function index()
    {
        if (auth()->user()->hasPermission('recruitment_notice')) {
            $recruitment_notices = RecruitmentNotice::orderBy('updated_at' , 'desc')->get();
            return view('Admin.recruitmentnotice.all', compact('recruitment_notices'));
        }
        return abort(401);
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application|\never
     */
    public function create()
    {
        if (auth()->user()->hasPermission('recruitment_notice')) {
            $provinces = Province::all();
            return view('Admin.recruitmentnotice.create', compact('provinces'));
        }
        return abort(401);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        $this->validate(request(), [
            'title' => 'required|string',
            'description' => 'nullable|string',
            'province_id' => 'required',
        ]);
        $inputs = $request->all();
        if($request->urlimage)
        {
            $inputs['image'] = Helper::moveImage($request->urlimage,'document');
        }
        unset($inputs['urlimage']);

        $recruitment_notice = RecruitmentNotice::create($inputs);
        if(auth()->user()->admin_representation_id)
        {
            $recruitment_notice->update(['representation_id' => auth()->user()->admin_representation_id]);
        }
        return redirect(route('recruitmentNotice.index'))->with([
            'create' => 'success'
        ]);
    }

    /**
     * @param RecruitmentNotice $recruitmentNotice
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application|\never
     */
    public function edit(RecruitmentNotice $recruitmentNotice)
    {
        if (auth()->user()->hasPermission('recruitment_notice')) {
            $provinces = Province::all();
            return view('Admin.recruitmentnotice.edit', compact('recruitmentNotice', 'provinces'));
        }
        return abort(401);
    }

    /**
     * @param Request $request
     * @param RecruitmentNotice $recruitmentNotice
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request, RecruitmentNotice $recruitmentNotice)
    {
        $this->validate(request(), [
            'title' => 'required|string',
            'description' => 'nullable|string',
            'province_id' => 'required',
        ]);
        $inputs = $request->all();
        if($request->urlimage)
        {
            Helper::deleteImage($recruitmentNotice->image);
            $inputs['image'] = Helper::moveImage($request->urlimage,'document');
        }
        unset($inputs['urlimage']);

        $recruitmentNotice->update($inputs);
        return redirect()->back();
    }

    /**
     * @param RecruitmentNotice $recruitmentNotice
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|\never
     */
    public function destroy(RecruitmentNotice $recruitmentNotice)
    {
        if (auth()->user()->hasPermission('recruitment_notice')) {
            $recruitmentNotice->delete();
            return redirect(route('recruitmentNotice.index'))->with([
                'delete' => 'success'
            ]);
        }
        return abort(401);
    }
}
