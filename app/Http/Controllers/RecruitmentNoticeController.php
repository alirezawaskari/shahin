<?php

namespace App\Http\Controllers;

use App\Models\RecruitmentNotice;
use App\Models\Resume;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class RecruitmentNoticeController extends Controller
{
    public function upload($file)
    {
        $year = Carbon::now()->year;
        $month = Carbon::now()->month;

        $filepath = "/upload/resume/{$year}/{$month}/";

        $rand = rand(1, 999);
        $filename = "{$rand}_" . $file->getClientOriginalName();
        $file->move(public_path($filepath), $filename);
        return $filepath . $filename;
    }
    public function index()
    {
        $recruitment_notices = RecruitmentNotice::where('status' , 1)->orderBy('updated_at' , 'desc')->get();
        return view('site.recruitmentNotice', compact('recruitment_notices'));
    }
    public function send_resume(Request $request)
    {
        $this->validate(request(), [
            'name' => 'required|string',
            'phone' => ['required'],
            'email' => 'nullable|email',
            'text' => 'nullable|string',
			'recruitment_notice_id' => 'required',
            'resume' => 'mimes:pdf,jpg,jpeg|max:10000',
			'text' => 'required'
        ]);
        $inputs = $request->all();
		$recruitment_notice = RecruitmentNotice::find($inputs['recruitment_notice_id']);

		if (is_null($recruitment_notice)) {
            $status = 404;
            return redirect()->back()->with([
              'resume_success' => 'error'
            ]);
		}

        unset($inputs['resume']);

        $resume = Resume::create($inputs);

        if ($request->file('resume')){
            $file = $this->upload($request->file('resume'));
            $resume->update(['resume' => $file]);
        }
        return redirect()->back()->with([
            'resume_success' => 'success'
        ]);
    }
}
