<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\Helper;
use App\Models\Representation;
use App\Models\Employee;
use App\Models\Role;
use App\Models\User;
use App\Models\UserPerformance;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Resources\EmployeeCollection;
use App\Http\Resources\WalletTransactionCollection;
use App\Models\Callcenter;
use App\Models\RecruitmentNotice;
use App\Models\Resume;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

class EmployeeController extends AdminController
{
    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application|\never
     */
    public function index()
    {
        $user = auth()->user();
        if ($user->isAbleTo('employee-detail')) {
            $can_manual_login = false;
            if ($user->isAbleTo('manual-login-user')){
                $can_manual_login = true;
            }
            return view('Admin.employee.all',compact('can_manual_login'));
        }

        return abort(401);
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Foundation\Application|\Illuminate\View\View
     */
    public function sellers()
    {
        $callcenter_roles= Role::where('name','like','%callcenter%')->get();
        $parents=Employee::where('status',1)->get();
            return view('Admin.employee.sellers',compact('callcenter_roles','parents'));
    }


    /**
     * @return EmployeeCollection
     */
    public function filter()
    {
        $employees = Employee::filter()->orderBy('updated_at', 'desc');

        return new EmployeeCollection($employees->paginate(20));
    }


    /**
     * @param Request $request
     * @return EmployeeCollection
     */
    public function sellerFilter(Request $request)
    {
        $employees = Employee::filter()->orderBy('updated_at', 'desc');

        $employees->whereHas('user', function ($query) use($request) {
            $query->whereHas('roles', function ($query) use($request) {
                if (isset($request->roles)){
                    $query->whereIn('id',$request->roles);
                }else{
                    $query->whereIn('name',array('callcenter','digital_superadmin-callcenter','superadmin-callcenter'));
                }
            });
        });

        return new EmployeeCollection($employees->paginate(20));
    }

    /**
     * @param Employee $employee
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function edit(Employee $employee)
    {
        $representations = Representation::whereStatus(true)->get();
        $employees=$employee::where('status',1)->get();
        $recruitment_notices = RecruitmentNotice::where('status', 1)->orderBy('updated_at', 'desc')->get();
        $resume = Resume::where('phone' , $employee->user->phone)->get()->last();
        $recruitment_notice_selected = !is_null($resume) ? $resume->recruitment_notice_id : null;
        if(session()->has('representation'))
        {
            $recruitment_notices = session()->get('representation')->recruitmentNotices()->get();
        }
        $employee->resume_detail = json_decode($employee->resume_detail , true);
        return view('Admin.employee.edit', compact('employee', 'representations','employees','recruitment_notices','recruitment_notice_selected'));
    }

    /**
     * @param Request $request
     * @param Employee $employee
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request, Employee $employee)
    {
        $user = $employee->user;
        $this->validate(request(), [
            'name' => 'string',
            'family' => 'string',
            'password' => 'nullable|string|min:6|confirmed',
            'show_status' => [Rule::in([0, 1])]
        ]);

        $inputs = $request->all();

        $employee_items = isset($inputs['employee']) ? $inputs['employee'] : [];
        $employee_items['contract_start_date'] = isset($employee_items['contract_start_date']) ? Helper::vertaToMiladi($employee_items['contract_start_date']) : '';
        $employee_items['contract_expiration_date'] = isset($employee_items['contract_expiration_date']) ? Helper::vertaToMiladi($employee_items['contract_expiration_date']) : '';
        $employee_items['parent_id'] =$request->parent_id == 'null'?null:$request->parent_id;
        if ($request->urlimgaccess) {
            Helper::deleteImage($employee->imgaccess);
            $employee_items['imgaccess'] = Helper::moveImage($request->urlimgaccess, 'document');
        }
        if ($request->urlimglastcertificate) {
            Helper::deleteImage($employee->imglastcertificate);
            $employee_items['imglastcertificate'] = Helper::moveImage($request->urlimglastcertificate, 'document');
        }
        if(isset($inputs['employee']))
        {
            $validate= Validator::make($inputs, [
                'sales_commitment' => 'numeric|min:0',
            ]);
            if ($validate->fails()){
                return redirect()->back()->with([
                    'failByMessage' => $validate->errors()->all(),
                ]);
            }
            $user->employee->update($employee_items);
            unset($inputs['employee']);
        }

        $role_id = $request->input('role_id') || $user->roles->count() == 0 ? $request->input('role_id') : $user->roles[0]->id;
        $employee->update([
            'show_status' => $request->show_status
        ]);

        if ($request->urlimgshenasname) {
            Helper::deleteImage($user->imgshenasname);
            $inputs['imgshenasname'] = Helper::moveImage($request->urlimgshenasname, 'document');
        }
        if ($request->urlimgmeli) {
            Helper::deleteImage($user->imgmeli);
            $inputs['imgmeli'] = Helper::moveImage($request->urlimgmeli, 'document');
        }
        if ($request->urlimgsignature) {
            Helper::deleteImage($user->imgsignature);
            $inputs['imgsignature'] = Helper::moveImage($request->urlimgsignature, 'document');
        }
        if ($request->urlavatar) {
            Helper::deleteImage($user->avatar);
            $inputs['avatar'] = Helper::moveImage($request->urlavatar, 'document');
        }

        unset($inputs['urlimgshenasname']);
        unset($inputs['urlimgsignature']);
        unset($inputs['urlimgmeli']);
        unset($inputs['urlavatar']);
        unset($inputs['urlimgaccess']);
        unset($inputs['urlimglastcertificate']);

        unset($inputs['role_id']);
        $inputs['wallet'] = (float)str_replace(',', '', $request->wallet);
        if (isset($inputs['password']) && $inputs['password'] == '') {
            unset($inputs['password']);
            unset($inputs['password_confirmation']);
        } elseif(isset($inputs['password'])) {
            $user->update(array_merge($inputs, ['password' => Hash::make($inputs['password'])]));
            unset($inputs['password']);
            unset($inputs['password_confirmation']);
        }
        $user->update($inputs);

        if ($role_id == 0) {
            if ($user->roles->count() != 0) {
                $user->detachRole($user->roles[0]->id);
            }
        } else {
            if ($user->roles->count() != 0) {
                $user->detachRole($user->roles[0]->id);
                $user->attachRole($role_id);
            } else {
                $user->attachRole($role_id);
            }
        }

        return redirect()->back()->with([
            'update' => 'success'
        ]);
    }

    /**
     * @param Employee $employee
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function financialReport(Employee $employee)
    {
        $user = $employee->user;
        $data_access_date = $employee->data_access_date ? $employee->data_access_date : now();
        $items = WalletTransaction::where('user_id', $user->id)->where('created_at', '>=', $data_access_date)->filter()->latest()->get()->each(function ($item) {
            $item->verta_created_at = verta($item->created_at)->format('Y-m');
        })->groupBy('verta_created_at')->map(function (Collection $item) {
            return [
                'year' => verta($item[0]->created_at)->format('Y'),
                'month' => verta($item[0]->created_at)->format('%B'),
                'amount_received' => WalletTransaction::whereBetween('created_at', [Helper::vertaTocarbon(verta($item[0]->created_at)->startMonth()), Helper::vertaTocarbon(verta($item[0]->created_at)->endMonth())])->whereType('0')->where('user_id', $item[0]->user_id)->sum('price'),
                'item_count' => UserPerformance::whereBetween('date', [Helper::vertaTocarbon(verta($item[0]->created_at)->startMonth()), Helper::vertaTocarbon(verta($item[0]->created_at)->endMonth()->addDays(1))])->where('user_id', $item[0]->user_id)->distinct('factor_item_id')->count('factor_item_id'),
                'total_price' => UserPerformance::whereBetween('date', [Helper::vertaTocarbon(verta($item[0]->created_at)->startMonth()), Helper::vertaTocarbon(verta($item[0]->created_at)->endMonth()->addDays(1))])->where('user_id', $item[0]->user_id)->sum('price'),
                'ancillary_costs' => UserPerformance::whereBetween('date', [Helper::vertaTocarbon(verta($item[0]->created_at)->startMonth()), Helper::vertaTocarbon(verta($item[0]->created_at)->endMonth()->addDays(1))])->where('user_id', $item[0]->user_id)->sum('ancillary_costs'),
                'user_tax' => UserPerformance::whereBetween('date', [Helper::vertaTocarbon(verta($item[0]->created_at)->startMonth()), Helper::vertaTocarbon(verta($item[0]->created_at)->endMonth()->addDays(1))])->where('user_id', $item[0]->user_id)->sum('tax'),
                'center_income' => WalletTransaction::whereBetween('created_at', [Helper::vertaTocarbon(verta($item[0]->created_at)->startMonth()), Helper::vertaTocarbon(verta($item[0]->created_at)->endMonth()->addDays(1))])->where('user_id', $item[0]->user_id)->whereType('1')->sum('price'),
                'created_at' => $item[0]->created_at->format("Y-m-d"),
            ];
        });

        //all user performance
        $walletTransactions = WalletTransaction::where('created_at', '>=', $data_access_date)->where('created_at', '<=', Helper::vertaTocarbon(verta()->startDay()->addDays(1)))->where('user_id', $user->id)->filter()->latest();
        $item = [];
        $item['total_income'] = WalletTransaction::where('created_at', '>=', $data_access_date)->where('created_at', '<=', Helper::vertaTocarbon(verta()->startDay()->addDays(1)))->whereStatus('1')->where('user_id', $user->id)->whereType('1')->sum('price');
        $item['amount_received'] = WalletTransaction::where('created_at', '>=', $data_access_date)->where('created_at', '<=', Helper::vertaTocarbon(verta()->startDay()->addDays(1)))->where('user_id', $user->id)->whereType('0')->sum('price');
        $item['credit_amount'] = $item['total_income'] - $item['amount_received'];
        $item['total_tax'] = UserPerformance::where('created_at', '>=', $data_access_date)->where('user_id', $user->id)->sum('tax');
        $item['amount_payable'] = $item['total_income'] - $item['amount_received'] - $item['total_tax'];
        $item['shaba_number'] = $user->employee->shaba_number;

        return view('Admin.employee.financialReport', compact('item', 'items', 'user', 'employee'));
    }

    /**
     * @param User $user
     * @param $key
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function walletTransaction(User $user, $key)
    {
        return view('Admin.employee.walletTransaction', compact('user', 'key'));
    }

    /**
     * @param Request $request
     * @param User $user
     * @return WalletTransactionCollection
     */
    public function walletTransactionFilter(Request $request, User $user)
    {
        $data_access_date = $user->employee->data_access_date ? $user->employee->data_access_date : now();
        //all walletTransactions user
        $wallet_transactions = WalletTransaction::where('created_at', '>=', $data_access_date)->where('created_at', '<=', Helper::vertaTocarbon(verta()->startDay()->addDays(1)))->where('user_id', $user->id)->filter()->latest();
        //filter walletTransactions user by created_at value
        if (isset($request->key) && $request->key != 'all') {
            $wallet_transactions = WalletTransaction::whereBetween('created_at', [Helper::vertaTocarbon(verta($request->key)->startMonth()), Helper::vertaTocarbon(verta($request->key)->endMonth())])->where('user_id', $user->id)->filter()->latest();
        }

        return new WalletTransactionCollection($wallet_transactions, $wallet_transactions->paginate(20));
    }

    /**
     * @param User $user
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function performanceDetail(User $user, Request $request)
    {
        $data_access_date = $user->employee->data_access_date ? $user->employee->data_access_date : now();

        $user_performances = UserPerformance::where('created_at', '>=', $data_access_date)->where('created_at', '<=', Helper::vertaTocarbon(verta()->startDay()->addDays(1)))->where('user_id', $user->id)->groupBy('factor_item_id')->filter()->latest()->get();
        if (isset($request->key) && $request->key != 'all') {
            $user_performances = UserPerformance::where('created_at', '>=', $data_access_date)->whereBetween('date', [Helper::vertaTocarbon(verta($request->key)->startMonth()), Helper::vertaTocarbon(verta($request->key)->endMonth())])->where('user_id', $user->id)->groupBy('factor_item_id')->filter()->latest()->get();
        }
        return view('Admin.employee.performanceDetail', compact('user', 'user_performances'));
    }

    /**
     * @param Request $request
     * @return void
     */
    public function acceptTerms(Request $request)
    {
        if ($request->terms == 'on'){
            auth()->user()->employee->update([
               'is_terms_accept'=>1
            ]);
            alert()->success(' عملیات موفق', 'ثبت با موفقیت انجام پذیرفت ');
            return redirect()->back();
        }
    }
    /**
     * @return \Illuminate\Contracts\View\View|\Illuminate\Contracts\View\Factory
     */
    public function financialReportEmployeeList()
    {
        if (auth()->user()->isAbleTo('financialReport_employee_list')) {
            return view('Admin.employee.financialReportEmployeeList');
        }

        return abort(401);
    }
    /**
     * @param \App\Models\Employee $employee
     * @return \Illuminate\Contracts\View\View|\Illuminate\Contracts\View\Factory
     */
    public function editResume(Employee $employee)
    {
        if (auth()->user()->employee->id == $employee->id) {
            $recruitment_notices = RecruitmentNotice::where('status', 1)->orderBy('updated_at', 'desc')->get();
            $resume = Resume::where('phone' , $employee->user->phone)->get()->last();
            $recruitment_notice_selected = !is_null($resume) ? $resume->recruitment_notice_id : null;
            if(session()->has('representation'))
            {
                $recruitment_notices = session()->get('representation')->recruitmentNotices()->get();
            }
            $employee->resume_detail = json_decode($employee->resume_detail , true);
            return view('Admin.employee.resume.index' , compact('employee' , 'recruitment_notices' , 'recruitment_notice_selected'));
        }

        return abort(401);
    }
    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function updateResume(Employee $employee , Request $request)
    {
        if (auth()->user()->isAbleTo('employee_resume_detail') || auth()->user()->employee->id == $employee->id)
        {
            $this->validate(request(), [
                'email' => 'nullable|email',
                'text' => 'nullable|string',
                'sex' => ['required',Rule::in(array(0,1))],
            ]);

            $recruitment_notice = RecruitmentNotice::find($request->recruitment_notice_id);

            if (is_null($recruitment_notice)) {
                return redirect()->back()->with([
                    'failByMessage' => 'انتخاب عنوان شغلی الزامی است.'
                ]);
            }

            $user = $employee->user;

            $callcenter_roles = Role::select('id')->where('card_id' , 1)->get();
            $description = " تکمیل فرم رزومه نهایی،
                 ،
              عنوان شغلی:
            {$recruitment_notice->title}";
            Callcenter::createTask($user, $description , $callcenter_roles);
            $this->updateEmployeeResume($employee , $request);

            return redirect()->back()->with([
                'successByMessage' => 'رزومه کاری شما با موفقیت ثبت شد.'
            ]);
        }
        return abort(401);
    }

    /**
     * @param $request
     * @param \App\Models\Employee $employee
     * @return void
     */
    public function updateEmployeeResume(Employee $employee , $request)
    {
        $bime_history = 0;
        if (!is_null($request->bime_history)) {
            $bime_history = $request->bime_history;
        }

        $employee->user->update([
            'sex' => $request->sex,
            'call' => $request->call,
            'melicode' => $request->melicode,
            'address' => $request->address,
            'shenasnamecode' => $request->shenasnamecode,
            'email' => $request->email,
        ]);

        $employee->update([
            'resume_detail' =>[
                "birth_certificate_issuing_place" => $request->input('birth_certificate_issuing_place'),
                "date_of_birth" => $request->input('date_of_birth'),
                "religion" => $request->input('religion'),
                "nationality" => $request->input('nationality'),
                "marital_status" => $request->input('marital_status'),
                "housing_situation" => $request->input('housing_situation'),
                "bime_history" => $bime_history,
                "work_type" => $request->work_type,
                "work_history" => $request->work_history,
                "text" => $request->text,
                "military_service_status" => $request->input('military_service_status'),
                "exemption_military_service" => $request->input('exemption_military_service'),
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
                "transfer_job" => $request->input('transfer_job'),
                "busy_now" => $request->input('busy_now'),
                "can_guarantee" => $request->input('can_guarantee'),
                "relative_familiarity" => $request->input('relative_familiarity'),
                "guarantee" => $request->input('guarantee'),
                "relative_family_type" => $request->input('relative_family_type'),
                "relative_family_name" => $request->input('relative_family_name'),
                "relative_family_part" => $request->input('relative_family_part'),
                "accept_rules" => $request->input('accept_rules')
            ]
        ]);
    }
}
