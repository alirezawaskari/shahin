<?php

namespace App\Http\Controllers\Admin;

use App\Classes\External\IranSms;
use App\Events\fastserviceCallcenter;
use App\Events\registerRequestNotification;
use App\Events\sendlinkFactor;
use App\Events\smsNotAnswer;
use App\Exports\CallcenterExport;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Resources\CallcenterCollection;
use App\Models\Agency;
use App\Models\Area;
use App\Models\Callcenter;
use App\Models\Category;
use App\Models\Center;
use App\Models\Factor;
use App\Models\FactorItem;
use App\Models\FinancialPlansType;
use App\Models\History;
use App\Models\Organization;
use App\Models\Province;
use App\Models\Reason;
use App\Models\RecruitmentNotice;
use App\Models\Representation;
use App\Models\Role;
use App\Models\Service;
use App\Models\SuspensionStandard;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CallcenterController extends Controller
{
    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function index()
    {
        $title = 'فهرست درخواست ها';
        $url = 'filter';
        $delete=false;
        if (auth()->user()->isAbleTo('callcenter-delete')){
            $delete = true;
        }
        return view('Admin.callcenter.all' , compact('title' ,'delete','url'));
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function desktop()
    {
        $title = 'میزکار درخواست ها';
        $url = 'desktop/filter';
        $delete=false;
        if (auth()->user()->isAbleTo('callcenter-delete')){
            $delete = true;
        }
         return view('Admin.callcenter.all' , compact('title' ,'delete', 'url'));
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function suspension()
    {
        $title = 'فهرست درخواست های معلق';
        $url = 'suspension/filter';
        $delete=false;
        if (auth()->user()->isAbleTo('callcenter-delete')){
            $delete = true;
        }
        return view('Admin.callcenter.all' , compact('title' ,'delete', 'url'));
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function paid()
    {
        $title = 'فهرست درخواست های پرداخت شده';
        $url = 'paid/filter';
        $delete=false;
        if (auth()->user()->isAbleTo('callcenter-delete')){
            $delete = true;
        }
        return view('Admin.callcenter.all' , compact('title' ,'delete', 'url'));
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function Cancellation()
    {
        $title = 'فهرست انصراف از دریافت پیامک';
        $url = 'cancellation/filter';
        $delete=false;
        if (auth()->user()->isAbleTo('callcenter-delete')){
            $delete = true;
        }
        return view('Admin.callcenter.all' , compact('title' ,'delete', 'url'));
    }

    /**
     * @param Request $request
     * @return CallcenterCollection|\Illuminate\Http\Response|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function filter(Request $request)
    {
        $callcenters = Callcenter::where('status' , '!=' , 11)->filter()->latest();

        if ($request->exel == 'on') {
            return (new CallcenterExport($callcenters))->download('callcenters.xlsx');
        }
        return new CallcenterCollection($callcenters->paginate(20));
    }

    //desktop for callcenter

    /**
     * @param Request $request
     * @return CallcenterCollection|\Illuminate\Http\Response|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function filterDesktop(Request $request)
    {
        $callcenters = Callcenter::filter()->where('user_id' , auth()->user()->id)->whereIn('status' , [0 , 4 , 6])->latest();

        if ($request->exel == 'on') {
            return (new CallcenterExport($callcenters))->download('callcenters.xlsx');
        }
        return new CallcenterCollection($callcenters->paginate(20));
    }

    /**
     * @param Request $request
     * @return CallcenterCollection|\Illuminate\Http\Response|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function filterSuspension(Request $request)
    {
        $callcenters = Callcenter::filter()->whereIn('status', [1 , 2 , 9])->latest();
        if ($request->exel == 'true') {
            return (new CallcenterExport($callcenters))->download('callcenters.xlsx');
        }
        return new CallcenterCollection($callcenters->paginate(20));
    }

    /**
     * @param Request $request
     * @return CallcenterCollection|\Illuminate\Http\Response|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function filterPaid(Request $request)
    {
        $callcenters = Callcenter::filter()->where('status', 3)->latest();
        if ($request->exel == 'true') {
            return (new CallcenterExport($callcenters))->download('callcenters.xlsx');
        }
        return new CallcenterCollection($callcenters->paginate(20));
    }

    /**
     * @param Request $request
     * @return CallcenterCollection|\Illuminate\Http\Response|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function filterCancellation(Request $request)
    {
        $callcenters = Callcenter::filter()->where('status', 11)->latest();
        if ($request->exel == 'true') {
            return (new CallcenterExport($callcenters))->download('callcenters.xlsx');
        }
        return new CallcenterCollection($callcenters->paginate(20));
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Foundation\Application|\Illuminate\Http\Response
     */
    public function tasks_requires()
    {
        $user = auth()->user();
        $role_users = Role::where('name', 'callcenter')->orWhere('name', 'presentore')->orWhere('name', 'influencer-marketing')->with('users')->get();
        $admin_users = User::filter()->where('level', 'admin')->withCount('tasks')->get();

        $services = Service::filter()->where('status' , 1)->get();
        $categories = Category::filter()->where('parent_id', 1)->where('isActive' , 1)->get();
        $permission = false;

        if (auth()->user()->center_id) {
            $services = Service::filter()->whereIn('id' , auth()->user()->center->services->pluck('id')->toArray())->get();
            $categories = Category::filter()->where('parent_id', 1)->where('id', auth()->user()->center->centerable_id)->get();
        }
        if($user->isAbleTo('record_payment'))
        {
            $permission = true;
        }
        if ($user->admin_representation) {
            $services = auth()->user()->admin_representation->services;
            $categories = auth()->user()->admin_representation->categories;
            $admin_users = User::filter()->where('level', 'admin')->where('admin_representation_id', $user->admin_representation->id)->withCount('tasks')->get();
        }

        $old_callcenters = [];

        foreach ($role_users as $users) {
            foreach ($users->users as $u) {
                if ($u->level == 'user') {
                    $old_callcenters[] = $u;
                }
            }
        }

        $callcenters = [];
        foreach ($admin_users as $u) {
            if ($user->isAbleTo('factor') || $user->isAbleTo('factor-detail')) {
                $callcenters[] = $u;
            }
        }

        return response([
            'data' => [
                'services' => $services->map(function ($item) {
                    $temp = [];
                    $temp['id'] = $item->id;
                    $temp['label'] = $item->title . ' - ' . number_format($item->price) . ' ریال';
                    return $temp;
                }),
                'categories' => $categories->map(function ($item) {
                    $temp = [];
                    $temp['id'] = $item->id;
                    $temp['label'] = $item->title . ' - ' . number_format($item->price) . ' ریال';
                    return $temp;
                }),
                'callcenters' => $callcenters,
                'old_callcenters' => $old_callcenters,
                'reason' => Reason::where('id', '!=', 0)->get(),
                'provinces' => Province::all(),
                'areas' => Area::all(),
                'centers' => Center::where('status' , 1)->get(),
                'representations' => Representation::all(),
                'organizations' => Organization::all(),
                'recruitmentNotices' => RecruitmentNotice::all(),
                'permission' => $permission,
            ]
        ]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Foundation\Application|\Illuminate\Http\Response
     */
    public function taskDetail(Request $request)
    {
        $task = Callcenter::find($request->id);
        $user = auth()->user();
        $detail = false;
        if ($user->isAbleTo('callcenter-detail')) {
            $detail = true;
        }

        $user = $task->type == 1 ? User::where('phone' , $task->sms->sender_number)->first() : $task->register;
        $user ? $task->update(['reg_id' => $user->id]) : '';
        $histories = Helper::userHistories($task->register);
        $tasks= isset($user->id) ? Callcenter::whereIn('status',array(0,1,2,3,4,6))->where('reg_id',$user->id)->latest()->get() : [];
        $tasks= isset($user->id) ? CallcenterCollection::make($tasks) : [];
        $factors=$user ? $user->factors->whereIn("status",array(0,1))->whereIn('title',array('register','factor'))->where('factor_item_id',null)->map(function ($factor) {
            $item = [];
            $item['resnumber'] = $factor->resnumber;
            $item['price'] = number_format($factor->price) . ' ریال';
            $item['type'] = $factor->type == 'online' ? 'پرداخت آنلاین' : 'کارت به کارت';
            $item['status'] = Factor::status($factor->status);
            $item['date'] = verta($factor->created_at)->format('%d %B ، %Y - H:i');
            $item['items'] = $factor->factorItems->map(function ($factor_item) {
                $item = [];
                $item['service'] = $factor_item->factoritemable->title;
                return implode(',', $item);
            });
            $item['representation'] = !is_null($factor->representation_id) ? $factor->representation->fa_name : 'مرکزی تهران';
            return $item;
        }) : [];
        return response([
            'data' => [
                'per_detail' => $detail,
                'histories' => $histories,
                'tasks'=>$tasks,
                'factors' => $factors,
            ]
        ]);
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function sendLink()
    {
        $provinces = Province::all();

        if (auth()->user()->center_id) {
            $service_categories = Category::filter()->where('parent_id', 2)->where('isActive' , 1)->whereIn('id', auth()->user()->center->services->pluck('category_id')->toArray())->get();
            $product_categories = Category::filter()->where('parent_id', 1)->where('id', auth()->user()->center->centerable_id)->get();
        }elseif (auth()->user()->admin_representation) {
            $service_categories = auth()->user()->admin_representation->services;
            $product_categories = auth()->user()->admin_representation->categories;
        }else{
            $product_categories = Category::filter()->where('parent_id', 1)->where('isActive' , 1)->get();
            $service_categories = Category::filter()->where('parent_id', 2)->where('isActive' ,1)->get();
        }

        if(auth()->user()->center && auth()->user()->center->centerable_type == Service::class)
        {
            $service_categories = Category::filter()->where('id', auth()->user()->center->centerable->category_id)->get();
        }

        return view('Admin.callcenter.sendLink', compact('provinces','service_categories','product_categories'));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Foundation\Application|\Illuminate\Http\Response
     */
    public function fetchUser(Request $request)
    {

        $phone = Helper::numberConverter($request->phone);
        $user = User::where('phone', $phone)->first();
        $histories = [];
        $factors = [];
        $tasks = [];
        if($user)
        {
            $histories = Helper::userHistories($user);
            $factors = $user->factors->whereIn("status",array(0,1))->whereIn('title',array('register','factor'))->where('factor_item_id',null)->map(function ($factor,$index)  {
                $item = [];
//        $item['resnumber'] = $factor->resnumber;
                $register_by=!is_null($factor->callcenter_id)?User::find($factor->callcenter_id):null;
                $item['register_by'] = !is_null($register_by)?$register_by->name.' '.$register_by->family:'سایت';
                $item['price'] = number_format($factor->price) . ' ریال';
                $item['type'] = $factor->type == 'online' ? 'پرداخت آنلاین' : 'کارت به کارت';
                $item['status'] = $factor->status ;
                $item['date'] = verta($factor->created_at)->format('%d %B ، %Y - H:i');
                $item['items'] = $factor->factorItems->map(function ($factor_item) {
                    $item = [];
                    $item['service'] = $factor_item->factoritemable->title;
                    return implode(',', $item);
                });
                $item['representation'] = !is_null($factor->representation_id) ? $factor->representation->fa_name : 'مرکزی تهران';
                return $item;
            });
            $tasks=Callcenter::whereIn('status',array(0,1,2,3,4,6))->where('reg_id',$user->id)->latest()->get();
            $tasks=CallcenterCollection::make($tasks);
        }

        $vip = $user && $user->vip && $user->vip->type == 'VIP' ? true : false;
        $sos = $user && $user->vip && $user->vip->type == 'SOS' ? true : false;
        $user['vip_user'] = $vip;
        $user['sos'] = $sos;

        return response(['operator' => true, 'data' => ['factors'=>$factors,'user' => $user, 'histories' => $histories,'tasks'=>$tasks]]);
    }

    //create & send link factor

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|void
     */
    public function registerForm(Request $request)
    {
        $status = self::getStatusFactorByPayType($request->payment_type);
        $price = 0;
        $cash_factor = [];
        $credit_factor = [];
        $phone = Helper::numberConverter($request->phone);
        $client = User::where('phone', $phone)->first();
        //create new user
        if (!$client) {
            $client = self::createUser($request);
        }else{
            $conduct=null;
            if ($request->challenging)
              $conduct='challenging';
            $client->update([
                'sex'=>$request->gender,
                'conduct'=>$conduct
            ]);
        }

        $payment_type = $request->payment_type != null ? $request->payment_type : 'online';
        if ($client) {
            //sum price factor cash or credit
            $services = $request->service ? $request->service : [];
            $categories = $request->product_category ? $request->product_category : [];
            $result_sum_price_factors = self::getSumPriceFactorItems($request , $services , $categories);
            if ( $payment_type == 'credit' ) {
                $result = User::checkCredit( $client, $result_sum_price_factors['sum_price_factors'] , $request->input( 'password' ) , 'register');
                if ( !$result[ 'success' ] ) {
                    return redirect()->back()->with( [
                        'message' => $result[ 'message' ]
                    ] );
                }
            }
            if ( $payment_type == 'wallet' ) {
                $result = User::checkWallet( $client, $price, $request->input( 'password' ) );
                if ( !$result[ 'success' ] ) {
                    return redirect()->back()->with( [
                        'message' => $result[ 'message' ]
                    ] );
                }
            }
            //create task.
            $task = self::createTask($request , $client , $payment_type);
            self::syncTaskItems($request , $task);
            $mobile_supervisor = $request->input('mobile_supervisor') ? $request->input('mobile_supervisor') : $phone;
            if (isset($request->send_factor)&&$request->send_factor==1) {
                //create factor for credit services or cash crategories
                if ($result_sum_price_factors['is_credit_factor']) {
                    $credit_factor = self::createCreditFactor($request, $client, $task, $services, $payment_type, $result_sum_price_factors, $status);
                }

                //create factor for cash services or cash crategories
                if ($result_sum_price_factors['is_cash_factor']) {
                    $cash_factor = self::createCashFactor($request, $client, $task, $services, $payment_type, $result_sum_price_factors, $status);
                }

                //save factor payment by any ways
                if ($result_sum_price_factors['is_credit_factor'] || $result_sum_price_factors['is_cash_factor']) {
                    //create factorItem for services
                    $create_service_factor_item_result = self::createServiceFactorItems($request, $services, $cash_factor, $credit_factor, $mobile_supervisor);
                    //create facorItem for categories
                    $create_category_factor_item_result = self::createCategoryFactorItems($create_service_factor_item_result, $request, $categories, $cash_factor, $credit_factor, $mobile_supervisor);
                    $payment_type = self::getPaymentType($cash_factor, $credit_factor);
                    //get cash factor representaation for change factor representaion
                    $cash_factor ? self::getFactorRepresentation($cash_factor) : '';
                    //get credit factor representaation for change factor representaion
                    $credit_factor ? self::getFactorRepresentation($credit_factor) : '';
                    self::updateFactorFieldIsSupervisorType($create_category_factor_item_result, $cash_factor, $credit_factor);
                    self::updateCallcenterFieldIsOnlinePayTypeFactor($task, $payment_type);
                    self::updateCallcenterFieldIsZeroPriceFactor($task, $result_sum_price_factors);
                }

                //send sms for online factors
                if ($payment_type == 'online') {
                    self::sendSmsFactor($task, $result_sum_price_factors, $cash_factor, $credit_factor,$services,$categories);
                } else {
                    $cash_factor ? Factor::savePayment($cash_factor) : '';
                    $credit_factor ? Factor::savePayment($credit_factor) : '';
                }
            }elseif (is_array($services) && count($services)>0){
                self::sendSmsLinkSite($request , $client , $task,$services);
            }
            Helper::vipUser($request->vip, $client->id);
            Helper::sosUser($request->sos, $client->id);
            if($request->callcenter)
            {
                $request->task = $task->id;
                self::transfer_form($request);
            }
            return redirect()->back()->with( [
                'create' => 'success'
            ] );
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Foundation\Application|\Illuminate\Http\Response
     */
    public function statusForm(Request $request)
    {
       $validate = Validator::make($request->all(), [
            'reason' => 'required',
            'task' => ['required',Rule::exists('callcenters','id')],
        ]);
        if ($validate->fails()){
            return response([
               'status'=>'error',
               'messages'=>$validate->errors()->all()
            ]);
        }
        $task = Callcenter::find($request->task);

        if($task->status == 3)
        {
            return response([
                'operator' => true,
                'data'=> ['histories'=>Helper::userHistories($task->register)],
                'status'=>'error',
                'messages'=>'همکار گرامی امکان ثبت یادآوری برای درخواست پرداخت شده وجود ندارد'
            ]);
        }

        $task->update([
            'status' => $request->reason == 0 ? 3 : 2,
            'description' => $request->description,
            'reason_id' => $request->reason,
            'date_expire' => $request->date,
        ]);
        //create history
        $description = !is_null($request->description) ? $request->description : Reason::find($request->reason)->title;
        //create history
        $status = $request->reason == 0 ? 3 : 2;
        History::create($task , $task->user_id , $request->reason , $status , $description);
        //transfer task if select callcenter
        if($request->callcenter)
        {
            self::transfer_form($request);
        }
        //send sms
        if($task->register && $task->reason_id == 6)
        {
            self::suspensionBasedOnStandards($request , $task);
            event(new smsNotAnswer($task->register , $task->callcenter , 'مرکز تماس'));
        }
        else
        {
            $task->suspensionStandard()->delete();
        }

        if($task->register)
        {
            if (Agency::where('phone', $task->register->phone)) {
                Agency::where('phone', $task->register->phone)->update(['updated_at' => now()]);
            }

            Helper::vipUser($request->vip, $task->register->id);
            Helper::sosUser($request->sos, $task->register->id);
        }

        return response([
            'operator' => true,
            'data'=> ['histories'=>Helper::userHistories($task->register)],
            'status'=>'success',
            'messages'=>'یادآور با موفقیت ثبت گردید !'
        ]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Foundation\Application|\Illuminate\Http\Response
     */
    public function transfer_form(Request $request)
    {
        $validate=Validator::make($request->all(), [
            'callcenter' => 'required',
        ]);
        if ($validate->fails()){
            return response([
                'status'=>'error',
                'messages'=>$validate->errors()->all()
            ]);
        }
        $callcenter = User::find($request->callcenter);
        $task = Callcenter::find($request->task);
        $task->update([
            'user_id' => $request->callcenter,
            'representation_id' => $callcenter->admin_representation_id,
        ]);
        $auth_user =  auth()->user();
        //create history
        $description = "درخواست توسط همکار $auth_user->name  $auth_user->family  به همکار $callcenter->name $callcenter->family انتقال پیدا کرد ";
        $status = $task->status;
        History::create($task , $request->callcenter , $request->reason , $status , $description);

        return response([
            'operator' => true,
            'messages'=>'انتقال با موفقیت انجام گرفت !',
            'status'=>'success'
        ]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Foundation\Application|\Illuminate\Http\Response
     */
    public function task_transfer(Request $request)
    {
       $validation = Validator::make($request->all(), [
            'type' => 'required',
            'startUser' => 'required_if:type,date,default',
            'endUser' => 'required',
            'startDate' => 'required_if:type,date',
            'endDate' => 'required_if:type,date',
            'count' => 'required_if:type,default,date',
            'id' => 'required_if:type,select'
        ]);
            if ($validation->fails()){
                return response(['status'=>'error','messages'=>$validation->messages()->all()]);
            }
        if ($request->type == 'default') {
            $callcenters = Callcenter::where('user_id', $request->startUser)->where('status', '!=', 3)->take($request->count)->get();
        } else if ($request->type == 'date') {
            $callcenters = Callcenter::where('user_id', $request->startUser)->where('status', '!=', 3)->whereBetween('created_at', [$request->startDate, $request->endDate])->take($request->count)->get();
        } else {
            $callcenters = Callcenter::find($request->id);
        }
        $end_user = User::find($request->endUser);

        foreach ($callcenters as $callcenter) {
            $callcenter->user_id = $request->endUser;
            $callcenter->representation_id = $end_user->admin_representation_id;

            $callcenter->save();
        }

        return response([
            'operator' => true,
            'status'=>'success',
            'messages'=>'انتقال با موفقیت انجام گرفت .'
        ]);
    }

    /**
     * @param Callcenter $callcenter
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Foundation\Application|\Illuminate\Http\Response
     */
    public function destroy(Callcenter $callcenter)
    {
        if (auth()->user()->isAbleTo('callcenter-delete')) {
            $callcenter->delete();
            return response([
                'operator' => true,
                'status'=>'success',
                'message'=>'حذف با موفقیت انجام گردید .'
            ]);
        }
        else
        {
            return response([
                'operator' => true,
                'status'=>'unauthorized',
                'message'=>'شما مجاز به انجام این کار نیستید.'
            ]);
        }

    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function delete()
    {
        $callcenters = Callcenter::onlyTrashed()->latest()->paginate(20);
        return view('Admin.callcenter.delete', compact('callcenters'));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function restore(Request $request)
    {
        $callcenter = Callcenter::onlyTrashed()->where('id', $request->id)->first();
        $callcenter->restore();

        return redirect()->back()->with([
            'update' => 'success'
        ]);
    }

    /**
     * @return string
     */
    private function getType()
    {
        if(auth()->user()->hasPermission('influencer-marketing'))
        {
            return '3';
        }
        if(auth()->user()->hasPermission('in-person-registration'))
        {
            return '4';
        }
        return '2';
    }

    /**
     * @param $payment_type
     * @return bool
     */
    private function getStatusFactorByPayType($payment_type)
    {
        return $payment_type == 'supervisor' || $payment_type == 'online' ? false : true;
    }

    /**
     * @param $cash_factor
     * @param $credit_factor
     * @return string
     */
    private function getPaymentType($cash_factor , $credit_factor)
    {
        $payment_type = $cash_factor ? $cash_factor->type : '';
        $payment_type = $credit_factor ? $credit_factor->type : $payment_type;

        return $payment_type;
    }

    /**
     * @param $request
     * @return mixed
     * @throws \Illuminate\Validation\ValidationException
     */
    private function createUser($request)
    {
        $request['phone'] = Helper::numberConverter($request->phone);
        $this->validate($request, [
            'name' => 'string|required',
            'family' => 'string|required',
            'gender'=>Rule::in(array(0,1)),
            'phone' => 'numeric|required|unique:users|unique:users,phone|regex:/(09)[0-9]{9}/|digits:11|',
            'province' => 'required',
            'city' => 'required'
        ]);

        $pass = rand(12345, 99999);
        $challenging= null;
        if ($request->challenging)
            $challenging='challenging';
        $user = User::create([
            'name' => $request->name,
            'family' => $request->family,
            'phone' => $request->phone,
            'province_id' => $request->province,
            'city_id' => $request->city,
            'area_id' => $request->area,
            'password' => Hash::make($pass),
            'active' => true,
            'level' => 'user',
            'sex' => $request->gender,
            'conduct' => $challenging,
            'callcenter_id' => auth()->user()->id,
            'representation_id' => auth()->user()->admin_representation ? auth()->user()->admin_representation->id : null,
        ]);
        //event(new fastregisterCallcenter($user, $pass));
        return $user;
    }

    /**
     * @param $request
     * @param $user
     * @param $payment_type
     * @return mixed
     */
    private function createTask($request , $user , $payment_type)
    {
        $id = Helper::getCallcenter($user->province_id , auth()->user()->admin_representation_id);
        $agent = !auth()->user()->hasPermission('callcenter') ? auth()->user()->id : null;
        $callcenter = ($request->request_type == "link" && $payment_type == "online" && !auth()->user()->hasPermission('callcenter')) ? $id : auth()->user()->id;
        $type_task = self::getType();

        if ($request->task) {
            $callcenter = Callcenter::find($request->task);
            $callcenter->update([
                'type' => $type_task,
                'reg_id' => $user->id,
                'request_type' => 'link',
                'agent_id' => $agent,
                'status' => $request->send_factor ? 1 : 0,
            ]);
        }
        elseif($request->other_task_id == 'new') {
                $callcenter = Callcenter::create([
                    'reg_id' => $user->id,
                    'user_id' => $callcenter,
                    'agent_id' => $agent,
                    'type' => $type_task,
                    'request_type' => $request->request_type,
                    'status' => $request->send_factor ? 1 : 0,
                    'description' => $request->description,
                    'representation_id' => auth()->user()->admin_representation ? auth()->user()->admin_representation->id : null,
                ]);
                //send a request registeration sms to the user
                $request->request_type == 'other' ? event(new registerRequestNotification($user)) : '';
        }else{
            $callcenter=  Callcenter::find($request->other_task_id);

            $callcenter->update([
                'description' => $request->description,
            ]);
        }

        $request->description ? History::create($callcenter , $callcenter->user_id , null , 2 , $callcenter->description) : '';
        return $callcenter;
    }

    /**
     * @param $request
     * @param $task
     */
    private function syncTaskItems($request , $task)
    {

        if (is_array($request->product_category) && count($request->product_category)>0)
        $task->categories()->attach($request->product_category);
        if (is_array($request->service) && count($request->service)>0)
        $task->services()->attach($request->service);
    }

    /**
     * @param $request
     * @param $services
     * @param $categories
     * @return array
     */
    private function getSumPriceFactorItems($request , $services , $categories)
    {
        $cash_price_tax = 0;
        $cash_price = 0;
        $credit_price_tax = 0;
        $credit_price = 0;
        $is_cash_factor = false;
        $is_credit_factor = false;
        $service_financial_plans_types = $request->service_financial_plans_type;
        $category_financial_plans_types = $request->cateory_financial_plans_type;

        //sum price factor cash services or credit services
        for ($i = 0; $i < count($services); $i++) {
            $service=Service::find($services[$i]);
            $service_financial_plans_type = FinancialPlansType::findOrFail($service_financial_plans_types[$i]);
            $service_pay_type = $service_financial_plans_type::getPaymentType($service_financial_plans_type->payment_type);

            if ($services[$i] != 'all' && $service_pay_type == '0') {
                //calculate factor price by financialPlansType selected
                if (isset($request->service_financial_plans_type[$i]) && $request->service_financial_plans_type[$i] != 'all'  ){
                    $service_financial_plans_type=FinancialPlansType::find($request->service_financial_plans_type[$i]);
                    $cash_price = $cash_price + $service_financial_plans_type->price;
                    $cash_price_tax = $cash_price_tax + ($service_financial_plans_type->price * $service->tax) / 100;
                }else{
                    $cash_price = $cash_price + $service->price;
                    $cash_price_tax = $cash_price_tax + ($service->price * $service->tax) / 100;
                }
                $is_cash_factor = true;
            } elseif($services[$i] != 'all') {
                //calculate factor price by financialPlansType selected
                if (isset($request->service_financial_plans_type[$i]) && $request->service_financial_plans_type[$i] != 'all' ){
                    $service_financial_plans_type=FinancialPlansType::find($request->service_financial_plans_type[$i]);
                    $credit_price = $credit_price +  $service_financial_plans_type->price;
                    $credit_price_tax = $credit_price_tax + ($service_financial_plans_type->price * $service->tax) / 100;
                }else{
                    $credit_price = $credit_price + $service->price;
                    $credit_price_tax = $credit_price_tax + ($service->price * $service->tax) / 100;
                }
                $is_credit_factor = true;
            }
        }
        //sum price factor cash categories or credit categories
        for ($i = 0; $i < count($categories); $i++) {
            $category_financial_plans_type = FinancialPlansType::findOrFail($category_financial_plans_types[$i]);
            $category_pay_type = $category_financial_plans_type::getPaymentType($category_financial_plans_type->payment_type);

            if ($categories[$i] != 'all' && $category_pay_type == '0') {
                //pay cash
                if($request->category_purchase_type[$i] == '0')
                {
                    //online cash product
                    $category = Category::find($categories[$i])->slug;
                    //event(new fastserviceCallcenter($user, $category , $factor ,auth()->user(), 'online'));
                }
                else
                {
                    //person cash product
                    if (isset($request->category_financial_plans_type[$i]) && $request->category_financial_plans_type[$i] != 'all'  ){
                        $category_financial_plans_type=FinancialPlansType::find($request->category_financial_plans_type[$i]);
                        $cash_price = $cash_price + $category_financial_plans_type->price;
                        $cash_price_tax = $cash_price_tax + ($category_financial_plans_type->price * $service->tax) / 100;
                    }else{
                        $cash_price_tax = $cash_price_tax + (Category::find($categories[$i])->price * Category::find($categories[$i])->tax) / 100;
                        $cash_price = $cash_price + Category::find($categories[$i])->price;
                        $is_cash_factor = true;
                    }
                }
            } elseif($categories[$i] != 'all') {
                //pay credit
                if (isset($request->category_financial_plans_type[$i]) && $request->category_financial_plans_type[$i] != 'all' ){
                    $category_financial_plans_type=FinancialPlansType::find($request->category_financial_plans_type[$i]);
                    $credit_price = $credit_price +  $category_financial_plans_type->price;
                    $credit_price_tax = $credit_price_tax + ($category_financial_plans_type->price * Category::find($categories[$i])->tax) / 100;
                }else{
                    $credit_price_tax = $credit_price_tax + (Category::find($categories[$i])->price * Category::find($categories[$i])->tax) / 100;
                    $credit_price = $credit_price + Category::find($categories[$i])->price;
                    $is_credit_factor = true;
                }
            }
        }

        $result = [
            'cash_price_tax' => $cash_price_tax,
            'cash_price' => $cash_price,
            'is_cash_factor' => $is_cash_factor,
            'credit_price_tax' => $credit_price_tax,
            'credit_price' => $credit_price,
            'is_credit_factor' => $is_credit_factor,
            'sum_price_factors' => $cash_price + $cash_price_tax + $credit_price + $credit_price_tax,
        ];
        return $result;
    }

    /**
     * @param $request
     * @param $user
     * @param $callcenter
     * @param $services
     * @param $payment_type
     * @param $result_factor_items
     * @param $status
     * @return mixed
     */
    private function createCreditFactor($request , $user , $callcenter , $services  , $payment_type , $result_factor_items , $status)
    {
        $price = $result_factor_items['credit_price'] + $result_factor_items['credit_price_tax'];

        $credit_factor = $callcenter->factors()->create([
            'confirm' => false,
            'user_id' => $user->id,
            'task_id' => $callcenter->id,
            'title' => 'register',
            'price' => $price,
            'tax' => $result_factor_items['credit_price_tax'],
            'count' => count($services),
            'status' => $price == 0 ? true : $status,
            'callcenter_id' => auth()->user()->id,
            'type' => $payment_type,
            'number_card' => $request->number_card,
            'payment_code' => $request->payment_code,
            'center_id' => auth()->user()->center_id,
            'representation_id' => auth()->user()->admin_representation ? auth()->user()->admin_representation->id : null,
            'expired_at' => now()->addDay(3),
        ]);
        //upload bill_image cash factor
        if ($request->url_bill_image) {
            $credit_factor->update(['bill_image' => Helper::moveImage($request->url_bill_image,'document')]);
        }

        $credit_factor->totalFactorItem()->create([
            'factor_id' => $credit_factor->id,
            'total_factor_item_status_id' => 0,
            'status_mali' => 0,
        ]);

        return $credit_factor;
    }

    /**
     * @param $request
     * @param $user
     * @param $callcenter
     * @param $services
     * @param $payment_type
     * @param $result_factor_items
     * @param $status
     * @return mixed
     */
    private function createCashFactor($request , $user , $callcenter , $services , $payment_type , $result_factor_items , $status)
    {
        $price = $result_factor_items['cash_price'] + $result_factor_items['cash_price_tax'];

        $cash_factor = $callcenter->factors()->create([
            'confirm' => false,
            'user_id' => $user->id,
            'task_id' => $callcenter->id,
            'title' => 'factor',
            'price' => $price,
            'tax' => $result_factor_items['cash_price_tax'],
            'count' => count($services),
            'callcenter_id' => auth()->user()->id,
            'type' => $payment_type,
            'status' => $price == 0 ? true : $status,
            'number_card' => $request->number_card,
            'payment_code' => $request->payment_code,
            'center_id' => auth()->user()->center_id,
            'representation_id' => auth()->user()->admin_representation ? auth()->user()->admin_representation->id : null,
            'expired_at' => now()->addDay(3),
        ]);
        //upload bill_image cash factor
        if ($request->url_bill_image) {
            $cash_factor->update(['bill_image' => Helper::moveImage($request->url_bill_image,'document')]);
        }
        return $cash_factor;
    }

    /**
     * @param $request
     * @param $services
     * @param $cash_factor
     * @param $credit_factor
     * @param $mobile_supervisor
     * @return array
     */
    private function createServiceFactorItems($request , $services , $cash_factor , $credit_factor , $mobile_supervisor)
    {
        $service_financial_plans_types = $request->service_financial_plans_type;
        $payment_type = $cash_factor ? $cash_factor->type : '';
        $payment_type = $credit_factor ? $credit_factor->type : '';

        $cash_factor_result['cash_factor_price'] = $cash_factor ? $cash_factor->price : 0;
        $cash_factor_result['cash_factor_status'] = $cash_factor ? $cash_factor->status : 0;
        $credit_factor_result['credit_factor_price'] = $credit_factor ? $credit_factor->price : 0;
        $credit_factor_result['credit_factor_status'] = $credit_factor ? $credit_factor->status : 0;

        for ($i = 0; $i < count($services); $i++) {
            //create cash factorItems
            $service_financial_plans_type = FinancialPlansType::findOrFail($service_financial_plans_types[$i]);
            $service_pay_type = $service_financial_plans_type::getPaymentType($service_financial_plans_type->payment_type);
            $service = Service::find($services[$i]);
            if($services[$i] != 'all' && $service_pay_type == '0')
            {
                $factor_item = $cash_factor->factorItems()->create([
                    'factoritemable_id' => $services[$i],
                    'factoritemable_type' => Service::class,
                    'financial_plans_type_id' => isset($service_financial_plans_types[$i]) && $service_financial_plans_types[$i] != 'all' ? $service_financial_plans_types[$i] : null,
                    'count' => 1,
                    'price' => $payment_type == "supervisor" ? 0 : $service->price + ($service->price * $service->tax) / 100,
                    'center_id' => auth()->user()->center_id ? auth()->user()->center_id : null,
                    'type' => $service_pay_type,
                    'purchase_type' => $request->service_purchase_type[$i],
                ]);
                $factor_item->update(['supervisor_id' => $factor_item->id]);
                if($cash_factor && $cash_factor->type == 'supervisor')
                {
                    $cash_factor_result = self::getCashFactorFieldIsSupervisorType($cash_factor , $mobile_supervisor , $factor_item);
                }
            }
            //create credit factorItems
            elseif($services[$i] != 'all')
            {
                $total_factor_item = $credit_factor->totalFactorItemReg;
                $factor_item = $credit_factor->factorItems()->create([
                    'factoritemable_id' => $services[$i],
                    'factoritemable_type' => Service::class,
                    'total_factor_item_id' => $total_factor_item->id,
                    'financial_plans_type_id' => isset($service_financial_plans_types[$i]) && $service_financial_plans_types[$i] != 'all' ? $service_financial_plans_types[$i] : null,
                    'count' => 1,
                    'price' => $payment_type == "supervisor" ? 0 : Service::find($services[$i])->price + (Service::find($services[$i])->price * Service::find($services[$i])->tax) / 100,
                    'center_id' => auth()->user()->center_id ? auth()->user()->center_id : null,
                    'type' => $service_pay_type,
                    'purchase_type' => $request->service_purchase_type[$i],
                ]);
                $factor_item->update(['supervisor_id' => $factor_item->id]);

                if($credit_factor && $credit_factor->type == 'supervisor')
                {
                    $credit_factor_result = self::getCreditFactorFieldIsSupervisorType($credit_factor , $mobile_supervisor , $factor_item);
                }
            }
        }

        return ['cash_factor_status' => $cash_factor_result['cash_factor_status'] , 'cash_factor_price' => $cash_factor_result['cash_factor_price'] , 'credit_factor_status' => $credit_factor_result['credit_factor_status'] , 'credit_factor_price' => $credit_factor_result['credit_factor_price']];
    }

    /**
     * @param $result
     * @param $request
     * @param $categories
     * @param $cash_factor
     * @param $credit_factor
     * @param $mobile_supervisor
     * @return array
     */
    private function createCategoryFactorItems($result , $request , $categories , $cash_factor , $credit_factor , $mobile_supervisor )
    {
        $category_financial_plans_types = $request->category_financial_plans_type;
        $payment_type = $cash_factor ? $cash_factor->type : '';
        $payment_type = $credit_factor ? $credit_factor->type : '';
        $cash_factor_result['cash_factor_price'] = $result['cash_factor_price'];
        $cash_factor_result['cash_factor_status'] = $result['cash_factor_status'];
        $credit_factor_result['credit_factor_price'] = $result['credit_factor_price'];
        $credit_factor_result['credit_factor_status'] = $result['credit_factor_status'];

        for ($i = 0; $i < count($categories); $i++)
        {
            $category_financial_plans_type = FinancialPlansType::findOrFail($category_financial_plans_types[$i]);
            $category_pay_type = $category_financial_plans_type::getPaymentType($category_financial_plans_type->payment_type);

            //create credit factorItems
            if($categories[$i] != 'all' && $category_pay_type == 1)
            {
                $total_factor_item = $credit_factor->totalFactorItemReg;
                $factor_item = $credit_factor->factorItems()->create([
                    'factoritemable_id' => $categories[$i],
                    'factoritemable_type' => Category::class,
                    'total_factor_item_id' => $total_factor_item->id,
                    'financial_plans_type_id' => isset($category_financial_plans_types[$i]) && $category_financial_plans_types[$i] != 'all' ? $category_financial_plans_types[$i] : null,
                    'count' => 1,
                    'price' => $payment_type == "supervisor" ? 0 : Category::find($categories[$i])->price + (Category::find($categories[$i])->price * Category::find($categories[$i])->tax) / 100,
                    'center_id' => auth()->user()->center_id ? auth()->user()->center_id : null,
                    'type' => $category_pay_type,
                    'purchase_type' => $request->category_purchase_type[$i],
                ]);
                $factor_item->update(['supervisor_id' => $factor_item->id]);
                if($credit_factor && $result['credit_factor_price'] == 0 && $credit_factor->type == 'supervisor')
                {
                    $credit_factor_result = self::getCreditFactorFieldIsSupervisorType($credit_factor , $mobile_supervisor , $factor_item);
                }
            }
            //create cash factorItems
            elseif($categories[$i] != 'all' && $request->category_purchase_type[$i] == 1)
            {
                $factor_item = $cash_factor->factorItems()->create([
                    'factoritemable_id' => $categories[$i],
                    'factoritemable_type' => Category::class,
                    'count' => 1,
                    'price' => $payment_type == "supervisor" ? 0 : Category::find($categories[$i])->price + (Category::find($categories[$i])->price * Category::find($categories[$i])->tax) / 100,
                    'center_id' => auth()->user()->center_id ? auth()->user()->center_id : null,
                    'type' => $category_pay_type,
                    'purchase_type' => $request->category_purchase_type[$i],
                ]);
                $factor_item->update(['supervisor_id' => $factor_item->id]);
                if($cash_factor && $result['cash_factor_price'] == 0 && $cash_factor->type == 'supervisor')
                {
                    $cash_factor_result = self::getCashFactorFieldIsSupervisorType($cash_factor , $mobile_supervisor , $factor_item);
                }
            }
        }

        return ['cash_factor_status' => $cash_factor_result['cash_factor_status'] , 'cash_factor_price' => $cash_factor_result['cash_factor_price'] , 'credit_factor_status' => $credit_factor_result['credit_factor_status'] , 'credit_factor_price' => $credit_factor_result['credit_factor_price']];
    }

    /**
     * @param $callcenter
     * @param $result_factor_items
     * @param $cash_factor
     * @param $credit_factor
     */
    private function sendSmsFactor($callcenter , $result_factor_items , $cash_factor , $credit_factor,$services,$categories)
    {

         $services_title=implode(' _ ',Service::whereIn('id',$services)->get()->pluck('title')->toArray());

        if ($result_factor_items['cash_price']) {
            //if created task now send link site

            event(new sendlinkFactor($callcenter->register, $cash_factor, auth()->user()->family ,'online'));
            $callcenter->update([
                'status' => $callcenter->status == 1 ? 1 : 0,
            ]);
        }

        if ($result_factor_items['credit_price']) {
            //if created task now send link site

            event(new sendlinkFactor($callcenter->register, $credit_factor, auth()->user()->family, 'online'));
            $callcenter->update([
                'status' => $callcenter->status == 1 ? 1 : 0,
            ]);
        }
        //create history
        $description = "لینک  {$services_title} ارسال گردید و در انتظار پرداخت قرار دارد";
        History::create($callcenter , auth()->user()->id , null , 1 , $description);
    }

    /**
     * @param $request
     * @param $client
     * @param $task
     */
    private function sendSmsLinkSite($request , $client , $task,array $services)
    {
        $services_title=implode(' _ ',Service::whereIn('id',$services)->get()->pluck('title')->toArray());
        //create history
        $description = "لینک صفحه {$services_title} جهت مطالعه شرایط و اطلاعات خدمات ارسال گردید .";
        History::create($task , $task->user_id , null , 0 , $description);
        $services=Service::whereIn('id',$request->service)->get();
        foreach ($services as $service ){
            event(new fastserviceCallcenter($client->phone, $service , null ,$task, 'callcenter'));
        }
    }

    /**
     * @param $cash_factor
     * @param $mobile_supervisor
     * @param $factor_item
     * @return array
     */
    private function getCashFactorFieldIsSupervisorType($cash_factor , $mobile_supervisor , $factor_item)
    {
        $result = FactorItem::setSupervisor($mobile_supervisor , $factor_item);
        if(isset($result['supervisor_factor_item']))
        {
            $cash_factor_price = 0;
            $cash_factor_status = true;
        }
        else
        {
            $cash_factor->type = 'online';
            $cash_factor_price = $cash_factor->price;
            $cash_factor_status = $cash_factor->status;
        }
        return ['cash_factor_status' => $cash_factor_status , 'cash_factor_price' => $cash_factor_price];
    }

    /**
     * @param $credit_factor
     * @param $mobile_supervisor
     * @param $factor_item
     * @return array
     */
    private function getCreditFactorFieldIsSupervisorType($credit_factor , $mobile_supervisor , $factor_item)
    {
        $result = FactorItem::setSupervisor($mobile_supervisor , $factor_item);

        if(isset($result['supervisor_factor_item']))
        {
            $credit_factor_price = 0;
            $credit_factor_status = true;
        }
        else
        {
            $credit_factor->type = 'online';
            $credit_factor_price = $credit_factor->price;
            $credit_factor_status = $credit_factor->status;
        }

        return ['credit_factor_status' => $credit_factor_status , 'credit_factor_price' => $credit_factor_price];
    }

    /**
     * @param $result
     * @param $cash_factor
     * @param $credit_factor
     */
    private function updateFactorFieldIsSupervisorType($result , $cash_factor , $credit_factor)
    {
        $cash_factor ? $cash_factor->update([
            'status' => $result['cash_factor_status'],
            'price' => $result['cash_factor_price']
        ]) : '';

        $credit_factor ? $credit_factor->update([
            'status' => $result['credit_factor_status'],
            'price' => $result['credit_factor_price']
        ]) : '';
    }

    /**
     * @param $callcenter
     * @param $result_factor_items
     */
    private function updateCallcenterFieldIsZeroPriceFactor($callcenter , $result_factor_items)
    {
        //update the callcenter status to paid(status == 3) when the factor price is zero.
        $callcenter->update([
            'status' => !$result_factor_items['credit_price'] && !$result_factor_items['cash_price'] ? 10 : $callcenter->status,
            'user_id' => !$result_factor_items['credit_price'] && !$result_factor_items['cash_price'] ? auth()->user()->id : $callcenter->user_id,
        ]);
    }

    /**
     * @param $callcenter
     * @param $payment_type
     */
    private function updateCallcenterFieldIsOnlinePayTypeFactor($callcenter , $payment_type)
    {
        $callcenter->update([
            'status' => $payment_type == "online" ? $callcenter->status : 3,
            'user_id' => $payment_type == "online" ? $callcenter->user_id : auth()->user()->id,
        ]);
    }
    /**
     * @param Request $request
     * @return response
     */
    public function closeTask(Request $request)
    {
        $task = Callcenter::find($request->task);

        if(auth()->user()->isAbleTo('close-task'))
        {
            //create history
            History::create($task , $task->user_id , null , 2 , $request->description);
            if($request->close_task_type == 'this_task')
            {
                $task->update(['status' => 5]);
            }
            else
            {
                $task->register->callcenters()->whereIn('status',[0,1,2,4,6])->update(['status' => 5]);
            }
            if ($request->sms_close_task){
                try {
                    IranSms::sendSingleSms('closetask',$task->register->phone,['param1'=>auth()->user()->phone,'param2'=>URL::to('/profile')]);
                }catch (\Exception $e){
                    Log::error($e->getMessage());
                }
            }

            return response([
                'operator' => true,
                'data'=> ['histories'=>Helper::userHistories($task->register)],
                'status'=>'success',
                'messages'=>'درخواست با موفقیت بسته شد.'
            ]);
        }
        return response([
            'operator' => true,
            'data'=> ['histories'=>Helper::userHistories($task->register)],
            'status'=>'Unauthorized',
            'messages'=>'شما مجاز به انجام این کار نیستید'
        ]);
    }
    /**
     * @param Request $request
     * @return response
     */
    public function followedTask(Request $request)
    {
        $task = Callcenter::find($request->task);

        if(auth()->user()->isAbleTo('followed-task'))
        {
            //create history
            History::create($task , $task->user_id , null , 2 , $request->description);
            $task->update(['status' => 10]);

            return response([
                'operator' => true,
                'data'=> ['histories'=>Helper::userHistories($task->register)],
                'status'=>'success',
                'messages'=>'وضعیت درخواست با موفقیت تغییر کرد.'
            ]);
        }
        return response([
            'operator' => true,
            'data'=> ['histories'=>Helper::userHistories($task->register)],
            'status'=>'Unauthorized',
            'messages'=>'شما مجاز به انجام این کار نیستید'
        ]);
    }
    /**
     * @param Factor $factor
     * @return void
     */
    private function getFactorRepresentation(Factor $factor)
    {
        $factor_items = $factor->factorItems;
        foreach($factor_items as $item)
        {
            $representation = Service::setRepresentation($item->factoritemable);
            if($representation == null)
            {
                break;
            }
        }
        self::changeFactorRepresentation($factor , $representation);
    }
    /**
     * @param Factor $factor
     * @param int $representation
     * @return void
     */
    private function changeFactorRepresentation(Factor $factor , $representation)
    {
        //change the factor representation if it has not been set before
        $factor->representation ? '' : $factor->update(['representation_id' => $representation]);
    }
    /**
     * @param Request $request
     * @param Callcenter $callcenter
     * @return void
     */
    private function suspensionBasedOnStandards(Request $request , Callcenter $callcenter)
    {
        if (!$callcenter->suspensionStandard()->exists()) {
            $suspension_standard = SuspensionStandard::first();
            $callcenter->suspensionStandard()->create(['suspension_standard_id' => $suspension_standard->id]);
        }
        else
        {
            $suspension_standard = self::getNextSuspensionStandard($callcenter);
            if(isset($suspension_standard->id))
            {
                $callcenter->suspensionStandard->update(['suspension_standard_id' => $suspension_standard->id]);
            }
            else
            {
                $request->task = $callcenter->id;
                $request->close_task_type = 'all_user_task';
                self::closeTask($request);
                $description = 'به دلیل عدم پاسخگویی کاربر، تمام درخواست های باز کاربر بسته شد.';
            }
        }
        //suspention task
        if(isset($suspension_standard->id))
        {
            if($suspension_standard->waitting_time < 1)
            {
                $callcenter->update(['date_expire'=> now()->addMinutes($suspension_standard->waitting_time*60)]);
            }
            else{
                $callcenter->update(['date_expire'=> now()->addHours($suspension_standard->waitting_time)]);
            }
            $description = $suspension_standard->name .' تماس گرفته شد ارتباط با کاربر برقرار نشد.';
        }
        //create history
        $status = $request->reason == 0 ? 3 : 2;
        History::create($callcenter , $callcenter->user_id , $request->reason , $status , $description);
    }
    /**
     * @param Callcenter $callcenter
     * @return mixed
     */
    private function getNextSuspensionStandard(Callcenter $callcenter)
    {
        $next_suspension_standard_id = SuspensionStandard::where('id', '>', $callcenter->suspensionStandard->suspension_standard_id)->min('id');
        $next_suspension_standard = SuspensionStandard::find($next_suspension_standard_id);
        return $next_suspension_standard;
    }

}

