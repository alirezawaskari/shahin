<?php

namespace App\Http\Controllers\Admin;

use App\Exports\RecruitmentNoticeExport;
use App\Exports\RecruitmentNoticExport;
use App\Helpers\Helper;
use App\Helpers\MediaLibraryHelper;
use App\Http\Resources\RecruitmentNoticeCollection;
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
            $recruitment_notice_delete_ability = false;
            if (auth()->user()->isAbleTo('recruitment-notice-destroy')){
                $recruitment_notice_delete_ability = true;
            }
            return view('Admin.recruitmentnotice.all', compact('recruitment_notice_delete_ability'));
        }
        return abort(401);
    }

    public function filter(Request $request)
    {


        if(auth()->user()->admin_representation_id)
        {
            $recruitment_notice = auth()->user()->admin_representation->recruitmentNotices()->filter()->latest();
        }else{
            $recruitment_notice = RecruitmentNotice::filter()->latest();
        }
        if ($request->excel == 'on') {
            return (new RecruitmentNoticeExport($recruitment_notice))->download('recruitment_notice.xlsx');
        }

        return new RecruitmentNoticeCollection($recruitment_notice->paginate(20));
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
        ]);
        $inputs = $request->all();
        unset($inputs['image'], $inputs['urlimage']);

        $recruitment_notice = RecruitmentNotice::create($inputs);
        MediaLibraryHelper::attachTo($recruitment_notice, $request->urlimage, 'image');
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
        ]);
        $inputs = $request->all();
        unset($inputs['image'], $inputs['urlimage']);

        $recruitmentNotice->update($inputs);
        MediaLibraryHelper::attachTo($recruitmentNotice, $request->urlimage, 'image');
        return redirect()->back();
    }

    /**
     * @param RecruitmentNotice $recruitmentNotice
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|\never
     */
    public function destroy( RecruitmentNotice $recruitmentNotice, Request $request)
    {
        if (auth()->user()->hasPermission('recruitment_notice')) {
            foreach ($recruitmentNotice->media as $media) {
                MediaLibraryHelper::deleteMedia($media);
            }
            $recruitmentNotice->delete();
            if ($request->ajax()) {
                return response([
                    'status' => 'success',
                    'message'=>'عملیات موفق'
                ]);
            }
            return redirect(route('recruitmentNotice.index'))->with([
                'delete' => 'success'
            ]);
        }
        if ($request->ajax()) {
            return response([
                'status' => 'unauthorized',
                'message' => 'شما مجاز به انجام این کار نیستید.'
            ]);
        }
        return abort(401);
    }
}
