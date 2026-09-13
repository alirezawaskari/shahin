<?php

namespace App\Http\Controllers\Admin;

use App\Events\userActivation;
use App\Models\Callcenter;
use App\Models\City;
use App\Exports\UsersExport;
use App\Helpers\Helper;
use App\Http\Resources\UserCollection;
use App\Models\Organization;
use App\Models\Representation;
use App\Models\User;
use App\Models\Work;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends AdminController
{
    use \App\Http\Controllers\Concerns\TransfersRecordsToBusiness;

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function index()
    {
        $title = 'فهرست کاربران';
        $url = 'user/filter';
        $user = auth()->user();

        $create = false;
        $delete = false;
        $organ = false;
        $can_manual_login = false;

        if (!$user->isAbleTo('user-detail')){
            abort(403);
        }
        if ($user->isAbleTo('user-create')){
            $create = true;
        }
        if ($user->isAbleTo('user-delete')){
            $delete = true;
        }
        if ($user->isAbleTo('manual-login-user')){
            $can_manual_login = true;
        }

        if ($user->admin_organization_id){
            $organ = true;
        }

        return view('Admin.user.all' , compact('title', 'url' ,'delete' , 'create', 'organ','can_manual_login'));
    }
    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function create()
    {
        $representations = Representation::whereStatus(true)->get();
        $organizations = Organization::whereStatus(true)->get();
        return view('Admin.user.create', compact('representations' , 'organizations'));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        $request['phone'] = Helper::numberConverter($request->phone);

        $this->validate(request(), [
            'name' => 'required|string',
            'family' => 'required|string',
            'phone' => 'required|regex:/^09[0-9]{9}$/|unique:users',
        ]);
        $inputs = $request->all();

        $inputs['wallet'] = (float) str_replace(',', '', $request->wallet);
        $inputs['credit'] = (float) str_replace(',', '', $request->credit);

        $organization = Organization::find($request->organization_id);

        if($organization->credit >= $inputs['credit'])
        {
            $organization->update([
                'credit' => $organization->credit - $inputs['credit']
            ]);
        }
        else
        {
            return redirect()->back()->with([
                'message' => 'اعتبار سازمان کافی نمی باشد.'
            ]);
        }

        $pass = rand(12345, 99999);

        $pass = Hash::make($pass);
        $array = [
            'password' => $pass,
        ];

        $user = User::create(array_merge($inputs, $array));
        //event(new fastregisterorganCallcenter($user, $inputs['password']));
        return redirect(route('user.index'))->with([
            'user_create' => 'success'
        ]);
    }

    /**
     * @param User $user
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function show(User $user)
    {
        $factors = $user->factors()->whereStatus(true)->get();
        $total = $factors->sum('price');
        return view('Admin.user.show', compact('user', 'factors', 'total'));
    }

    /**
     * @param User $user
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function edit(User $user)
    {
        $representations = Representation::whereStatus(true)->get();
        return view('Admin.user.edit', compact('user', 'representations'));
    }

    /**
     * @param Request $request
     * @param User $user
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request, User $user)
    {

        $this->validate(request(), [
            'name' => 'required|string',
            'family' => 'required|string',
            'diseases' => 'nullable|array',
            'diseases.*' => 'nullable|integer|exists:diseases,id',
//            'wallet' => 'required',
            'password' => 'nullable|string|min:6|confirmed',
        ]);
        // فیلد «کسب و کار» فرم: تغییرش یعنی انتقال همین کاربر به کسب و کار دیگر.
        $businessTransfer = $this->applyInlineBusinessTransfer(
            $request,
            'user',
            [$user->id],
            $user->ghorfeOnlineLists->modelKeys()
        );

        $inputs = $request->all();
        // ستون جدول کاربران نیست؛ انتقال بالاتر انجام شده است.
        unset($inputs['destination_business_id']);
        $role_id = $request->input('role_id') || $user->roles->count() == 0 ? $request->input('role_id') : $user->roles[0]->id;
        // Spatie: KYC/avatar images arrive as temp media ids; attachTo replaces the single
        // file in each collection. Keep them out of the mass-assign ($inputs) below.
        if ($request->urlimgshenasname) {
            \App\Helpers\MediaLibraryHelper::attachTo($user, $request->urlimgshenasname, 'imgshenasname');
        }
        if ($request->urlimgmeli) {
            \App\Helpers\MediaLibraryHelper::attachTo($user, $request->urlimgmeli, 'imgmeli');
        }
        if ($request->urlavatar) {
            \App\Helpers\MediaLibraryHelper::attachTo($user, $request->urlavatar, 'avatar');
        }
        if ($request->urlimgturnover) {
            \App\Helpers\MediaLibraryHelper::attachTo($user, $request->urlimgturnover, 'imgturnover');
        }
        unset(
            $inputs['role_id'],
            $inputs['imgshenasname'], $inputs['imgmeli'], $inputs['avatar'], $inputs['imgturnover'],
            $inputs['urlimgshenasname'], $inputs['urlimgmeli'], $inputs['urlavatar'], $inputs['urlimgturnover']
        );

        $inputs['wallet'] = (float) str_replace(',', '', $request->wallet);

        if ($inputs['password'] == '') {
            unset($inputs['password']);
            unset($inputs['password_confirmation']);
            $user->update($inputs);
        } else {
            $user->update(array_merge($inputs, ['password' => Hash::make($inputs['password'])]));
        }

        if ($role_id == 0) {
            if ($user->roles->count() != 0) {
                $user->removeRole($user->roles[0]->id);
            }
        } else {
            $user->syncRoles([$role_id]);
        }
        if($user->level == 'admin' || $user->level == 'ghorfeAdmin')
        {
            $employee = Employee::firstOrCreate(['user_id' => $user->id]);
            $employee->update(['status'=> 1, 'contract_start_date' => ($employee->contract_start_date ? $employee->contract_start_date : now())]);
            !auth()->user()->admin_representation_id ? $user->update(['admin_representation_id' , auth()->user()->admin_representation_id]) : '';
        }
        if (($request->level != 'admin' || $user->level != 'ghorfeAdmin') && $user->employee()->exists() &&  $user->employee->status != 0 ){
            Employee::where('user_id' , $user->id)->update([
               'status'=>0
             ]);
        }
        if (isset($request->diseases))
            $user->diseases()->sync($request->diseases);
        Helper::vipUser($request->vip , $user->id);
        Helper::sosUser($request->sos , $user->id);

        return redirect()->back()->with(array_merge(
            ['user_update' => 'success'],
            $this->businessTransferFlash($businessTransfer)
        ));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Foundation\Application|\Illuminate\Http\Response
     * @throws \Illuminate\Validation\ValidationException
     */
    public function user_manager(Request $request)
    {
        $this->validate($request , [
            'type' => 'required',
        ]);

        if ($request->type == 'delete'){
            foreach ($request->id as $item){
                $user = User::find($item);
                $user->factors()->delete();
                $user->wallets()->delete();
                $user->callcenters()->delete();
                $user->works()->delete();
                $callcenters = Callcenter::where('reg_id', $user->id)->get();
                foreach ($callcenters as $callcenter) {
                    $callcenter->delete();
                }
                $user->delete();
            }
        }

        return response([
            'operator' => true,
        ]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|void
     */
    public function cityselect(Request $request)
    {
        if ($request->ajax()) {
            $id = $request->input('id');
            if (City::where('province_id', $id)->get()->count() > 0) {
                $cities = City::where('province_id', $id)->get();
                return response()->json(['count' => 'available', 'cities' => $cities]);
            }
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|void
     */
    public function userselect(Request $request)
    {
        if ($request->ajax()) {
            $text = $request->input('text');
            $users = User::where('family', 'LIKE', '%' . $text . '%')->where('phone', 'LIKE', '%' . $text . '%')->get();
            if ($users->count() > 0) {
                return response()->json(['count' => 'available', 'users' => $users]);
            }
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function work(Request $request)
    {
        $status = $request->input('status');
        $user = auth()->user();

        if ($status == '0') {

            Work::create([
                'user_id' => $user->id,
                'type' => 1,
            ]);
            $user->work_status = 1;
            $user->save();

            return redirect()->back()->with([
                'callcenter_work' => 'start'
            ]);
        } else {

            Work::create([
                'user_id' => $user->id,
                'type' => 0,
            ]);
            $user->work_status = 0;
            $user->save();

            return redirect()->back()->with([
                'callcenter_work' => 'finish'
            ]);
        }
    }

    /**
     * @param Request $request
     * @return UserCollection|\Illuminate\Http\Response|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function filter(Request $request)
    {
        $users = User::filter();
        if($request->level !== 'all'){
            $users->where('level', $request->level);
        }
        if ($request->exel == 'on') {
            return (new UsersExport($users))->download('users.xlsx');
        }

        return new UserCollection($users->latest()->paginate(20));
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function admin()
    {
        if (auth()->user()->admin_representation) {
            $users = User::where('representation_id', auth()->user()->admin_representation->id)->whereLevel('admin')->latest()->paginate(20);
        } else {
            $users = User::whereLevel('admin')->latest()->paginate(20);
        }
        return view('Admin.user.admin', compact('users'));
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function delete()
    {
        $users = User::onlyTrashed()->latest()->paginate(20);
        return view('Admin.user.delete' , compact('users'));
    }

    /**
     * restore user
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function restore(Request $request)
    {
        $user = User::onlyTrashed()->where('id' , $request->id)->first();
        $user->restore();
        $user->employee()->withTrashed()->restore();
        $user->factors()->withTrashed()->restore();
        $user->wallets()->withTrashed()->restore();
        $user->callcenters()->withTrashed()->restore();
        $user->works()->withTrashed()->restore();
        $callcenters = Callcenter::onlyTrashed()->where('reg_id', $user->id)->get();
        foreach ($callcenters as $callcenter) {
            $callcenter->restore();
        }
        return redirect()->back()->with([
            'update' => 'success'
        ]);
    }

    /**
     * fetch all histories of user
     * @param \App\Models\User $user
     * @return mixed
     */
    public function fetchUserHistories(User $user)
    {
        return response(['data'=>Helper::userHistories($user)]);
    }

    /**
     * destroy user
     * @param User $user
     * @return void
     */
    public function destroy(User $user)
    {
        $user->employee()->delete();
        $user->factors()->delete();
        $user->wallets()->delete();
        $user->callcenters()->delete();
        $user->works()->delete();
        $callcenters = Callcenter::where('reg_id', $user->id)->get();
        foreach ($callcenters as $callcenter) {
            $callcenter->delete();
        }
        $user->delete();

        return response(['status'=>'success','message'=>'کاربر با موفقیت حذف گردید !']);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Foundation\Application|\Illuminate\Http\Response
     */
    public function sendPassword(Request $request)
    {

        $user = User::where('phone' , $request->phone)->first();
        if($user)
        {
            $pass = rand(123456, 999999);
            $hash_pass = Hash::make($pass);
            $user->update(['password' => $hash_pass]);
            event(new userActivation($user , $pass));

            return response(['status' => 'success' , 'message' => 'با موفقیت ارسال گردید.']);
        }
        return response(['status' => 'error' , 'message' => 'شماره مورد نظر در سامانه وجود ندارد']);
    }

    /**
     * @param User $user
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application|\never
     */
    public function creditDetail(User $user)
    {
        if(auth()->user()->isAbleTo('user-credit-detail'))
        {
            $credit_details = $user->creditDetails;
            return view('Admin.user.creditDetail' , compact('user' , 'credit_details'));
        }
        return abort(401);
    }

    /**
     * manual login user by admin panel
     * @param User $user
     * @return \Illuminate\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function login(User $user)
    {
        if (!auth()->user()->hasPermission('manual-login-user')) {
            abort(403);
        }
        auth()->login($user);
        return redirect('/');
    }
     /**
     * @param Request $request
     * @return void
     */
    public function fetchUser(Request $request)
    {
        return User::createUser($request);
    }
}
