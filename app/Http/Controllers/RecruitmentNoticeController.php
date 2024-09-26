<?php

namespace App\Http\Controllers;

use App\Helpers\Helper;
use App\Models\Callcenter;
use App\Models\Province;
use App\Models\RecruitmentNotice;
use App\Models\Resume;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

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
        $recruitment_notices = RecruitmentNotice::where('status', 1)->orderBy('updated_at', 'desc')->get()->groupBy('province_id');
        if(session()->has('representation'))
        {
            $recruitment_notices = session()->get('representation')->recruitmentNotices()->get()->groupBy('province_id');
        }
        return view('site.recruitmentNotice', compact('recruitment_notices'));
    }
    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function registerBasicInformation(Request $request)
    {
        $request['phone'] = Helper::numberConverter($request->phone);
        $validate = Validator::make($request->all(), [
            'name' => 'required|string',
            'family' => 'required|string',
            'phone' => 'numeric|required|regex:/(09)[0-9]{9}/|digits:11|',
            'recruitment_notice_title' => 'required',
            'resume' => 'mimes:pdf,jpg,jpeg|max:10000',
        ]);

        if ($validate->fails()){
            return response([
                'status'=>'error',
                'messages'=>$validate->errors()->all()
            ]);
        }
        $recruitment_notice = $request->recruitment_notice_title;
        //check if user not exist -> create user
        User::createUser($request);
        $user = User::where('phone',$request->phone)->first();
        $callcenter_roles = Role::select('id')->where('card_id' , 1)->get();
        $description = "جویای کار،
        عنوان شغلی:
       {$recruitment_notice}";
        Callcenter::createTask($user, $description , $callcenter_roles);

        if ($request->ajax()) {
            return response([
                'status'=>'success',
                'messages'=>'موفقیت ارسال شد .'
            ]);
        }
        return redirect()->back()->with([
            'resume_success' => 'success'
        ]);
    }
    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function send_resume(Request $request)
    {
        $request['phone'] = Helper::numberConverter($request->phone);

        $validate = Validator::make($request->all(), [
            'name' => 'required|string',
            'family' => 'required|string',
            'phone' => 'numeric|required|regex:/(09)[0-9]{9}/|digits:11|',
            'sex' => [Rule::in(array(0,1))],
            'recruitment_notice_id' => 'required',
            'resume' => 'mimes:pdf,jpg,jpeg|max:10000',
        ]);

        if ($validate->fails()){
            return response([
                'status'=>'error',
                'messages'=>$validate->errors()->all()
            ]);
        }
        $inputs = $request->all();
        $recruitment_notice = RecruitmentNotice::find($inputs['recruitment_notice_id']);

        if (is_null($recruitment_notice)) {
            return redirect()->back()->with([
                'resume_success' => 'error'
            ]);
        }

        unset($inputs['resume']);

        //check if user not exist -> create user
        User::createUser($request);
        $user = User::where('phone',$request->phone)->first();
        $resume = $this->createResume($request);
        if ($request->file('resume')) {
            $file = $this->upload($request->file('resume'));
            $resume->update(['resume' => $file]);
        }
        $callcenter_roles = Role::select('id')->where('card_id' , 1)->get();
        $province = $recruitment_notice->province ? $recruitment_notice->province->name : 'استخدام کشوری (دورکاری)';
        $description = "جویای کار،
             {$province}
             ،
          عنوان شغلی:
        {$recruitment_notice->title}";
        Callcenter::createTask($user, $description , $callcenter_roles);

        if ($request->ajax()) {
            return response([
                'status'=>'success',
                'messages'=>'با موفقیت ارسال شد .'
            ]);
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
        $resume = Resume::create([
            'recruitment_notice_id' => $request->recruitment_notice_id,
            'name' => $request->name . ' ' . $request->family,
            'phone' => $request->phone,
            'work_history' => $request->work_history,
        ]);
        $resume->update(['representation_id' => $resume->recruitment_notice->representation_id]);
        return $resume;
    }
}
