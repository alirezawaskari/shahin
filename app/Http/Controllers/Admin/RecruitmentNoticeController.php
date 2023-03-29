<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\Helper;
use App\Models\Province;
use App\Models\RecruitmentNotice;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Arr;

class RecruitmentNoticeController extends AdminController
{
    public function index()
    {
        if (auth()->user()->hasPermission('recruitment_notice')) {
            $recruitment_notices = RecruitmentNotice::orderBy('updated_at' , 'desc')->get();
            return view('Admin.recruitmentnotice.all', compact('recruitment_notices'));
        }
        return abort(401);
    }

    public function create()
    {
        if (auth()->user()->hasPermission('recruitment_notice')) {
            $provinces = Province::all();
            return view('Admin.recruitmentnotice.create', compact('provinces'));
        }
        return abort(401);
    }

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

        RecruitmentNotice::create($inputs);

        return redirect(route('recruitmentNotice.index'))->with([
            'create' => 'success'
        ]);
    }

    public function edit(RecruitmentNotice $recruitmentNotice)
    {
        if (auth()->user()->hasPermission('recruitment_notice')) {
            $provinces = Province::all();
            return view('Admin.recruitmentnotice.edit', compact('recruitmentNotice', 'provinces'));
        }
        return abort(401);
    }

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
