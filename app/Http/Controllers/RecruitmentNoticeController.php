<?php

namespace App\Http\Controllers;

use App\Helpers\Helper;
use App\Models\Callcenter;
use App\Models\RecruitmentNotice;
use App\Models\Resume;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class RecruitmentNoticeController extends Controller
{
    /**
     * @param $file
     * @return string
     */
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

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function index()
    {
        $recruitment_notices = RecruitmentNotice::where('status' , 1)->orderBy('updated_at' , 'desc')->get();
        return view('site.recruitmentNotice', compact('recruitment_notices'));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function send_resume(Request $request)
    {
        $this->validate(request(), [
            'name' => 'required|string',
            'family' => 'required|string',
            'phone' => 'numeric|required|regex:/(09)[0-9]{9}/|digits:11|',
            'email' => 'nullable|email',
            'text' => 'nullable|string',
			'recruitment_notice_id' => 'required',
            'resume' => 'mimes:pdf,jpg,jpeg|max:10000',
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

        //check if user not exist -> create user
        $user = User::where('phone', $request->phone)->first();
        if (is_null($user)) {
            //create user
            $user = $this->createUser($request);
        }
        $this->createTask($user,$recruitment_notice);
        $resume = $this->createResume($request);

        if ($request->file('resume')){
            $file = $this->upload($request->file('resume'));
            $resume->update(['resume' => $file]);
        }
        return redirect()->back()->with([
            'resume_success' => 'success'
        ]);
    }

    /**
     * @param $request
     * @return mixed
     */
    public function createResume($request)
    {
        $bime_history=0;
        if (!is_null( $request->mibe_history))
        {
            $bime_history=$request->mibe_history;
        }
        $resume = Resume::create([
            'recruitment_notice_id' => $request->recruitment_notice_id,
            'name' => $request->name . ' ' . $request->family,
            'phone' => $request->phone,
            'email' => $request->email,
            'bime_history' => $bime_history,
            'work_type' => $request->work_type,
            'work_history' => $request->work_history,
            'text' => $request->text
        ]);
        return $resume;
    }

    /**
     * @param $request
     * @return mixed
     */
    private function createUser($request)
    {
        $pass = rand(12345, 99999);
        $phone = Helper::numberConverter($request->phone);

        return User::create([
            'name' => $request->name,
            'family' => $request->family,
            'phone' => $phone,
            'password' => Hash::make($pass),
            'active' => true,
            'level' => 'user',
        ]);
    }

    /**
     * @param $user
     * @return void
     */
    private function createTask(User $user,RecruitmentNotice $recruitmentNotice): void
    {
        $callcenter_user_id = User::where('level', 'admin')->where('active', 1)->whereHas('roles', function ($query) {
            $query->where('name', 'superadmin');
        })->first()->id;

        $description = "جویای کار،
          عنوان شغلی:
         {$recruitmentNotice->title}";
        Callcenter::create([
            'reg_id' => $user->id,
            'user_id' => $callcenter_user_id,
            'agent_id' => null,
            'type' => 0,
            'request_type' => 'other',
            'status' => 0,
            'description' => $description
        ]);
    }

}
