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
        $validate = Validator::make($request->all(), [
            'name' => 'required|string',
            'family' => 'required|string',
            'phone' => 'numeric|required|regex:/(09)[0-9]{9}/|digits:11|',
            'recruitment_notice_title' => 'required',
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
        $callcenter_roles = Role::select('id')->where('card_id' , 6)->get();
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
        $validate = Validator::make($request->all(), [
            'name' => 'required|string',
            'family' => 'required|string',
            'phone' => 'numeric|required|regex:/(09)[0-9]{9}/|digits:11|',
            'email' => 'nullable|email',
            'text' => 'nullable|string',
            'sex' => ['string',Rule::in(array('خانم','آقا'))],
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

        $callcenter_roles = Role::select('id')->where('card_id' , 6)->get();
        $province = $recruitment_notice->province ? $recruitment_notice->province->name : 'استخدام کشوری (دورکاری)';
        $description = "جویای کار،
             {$province}
             ،
          عنوان شغلی:
        {$recruitment_notice->title}";
        Callcenter::createTask($user, $description , $callcenter_roles);
        $resume = $this->createResume($request);

        if ($request->file('resume')) {
            $file = $this->upload($request->file('resume'));
            $resume->update(['resume' => $file]);
        }

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
     * @param $request
     * @return mixed
     */
    public function createResume($request)
    {
        $bime_history = 0;
        if (!is_null($request->mibe_history)) {
            $bime_history = $request->mibe_history;
        }
        $resume = Resume::create([
            'recruitment_notice_id' => $request->recruitment_notice_id,
            'name' => $request->name . ' ' . $request->family,
            'phone' => $request->phone,
            'email' => $request->email,
            'bime_history' => $bime_history,
            'work_type' => $request->work_type,
            'work_history' => $request->work_history,
            'text' => $request->text,
            'detail' =>[
                "number_shenasname" => $request->input('number_shenasname'),
                "birth_certificate_issuing_place" => $request->input('birth_certificate_issuing_place'),
                "national_number" => $request->input('national_number'),
                "date_of_birth" => $request->input('date_of_birth'),
                "religion" => $request->input('religion'),
                "nationality" => $request->input('nationality'),
                "marital_status" => $request->input('marital_status'),
                "sex" => $request->input('sex'),
                "address" => $request->input('address'),
                "housing_situation" => $request->input('housing_situation'),
                "telephone" => $request->input('telephone'),
                "military_service_status" => $request->input('military_service_status'),
                "exemption_military_service_" => $request->input('exemption_military_service_'),
                "exemption_military_service_type" => $request->input('exemption_military_service_type'),
                "exemption_military_service_end_date" => $request->input('exemption_military_service_end_date'),
                "military_service_location" => $request->input('military_service_location'),
                "military_service_end_date" => $request->input('military_service_end_date'),
                "degree_of_education" => $request->input('degree_of_education'),
                "field_of_Study" => $request->input('field_of_Study'),
                "total_average" => $request->input('total_average'),
                "education_location" => $request->input('education_location'),
                "education_end_date" => $request->input('education_end_date'),
                "cooperation_type" => $request->input('cooperation_type'),
                "description_half_time" => $request->input('description_half_time'),
                "en_lang_quality" => $request->input('en_lang_quality'),
                "computer_skill" => $request->input('computer_skill'),
                "start_work_date" => $request->input('start_work_date'),
                "rights_requested" => (float)str_replace(',', '', Helper::numberConverter($request->input('rights_requested'))),
                "busy_now" => $request->input('busy_now'),
                "can_guarantee" => $request->input('can_guarantee'),
                "guarantee" => $request->input('guarantee'),
                "relative_family_type" => $request->input('relative_family_type'),
                "relative_family_name" => $request->input('relative_family_name'),
                "relative_family_part" => $request->input('relative_family_part'),
                "accept_rules" => $request->input('accept_rules')
            ]
        ]);
        $resume->update(['representation_id' => $resume->recruitment_notice->representation_id]);
        return $resume;
    }
}
