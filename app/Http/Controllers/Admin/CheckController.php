<?php

namespace App\Http\Controllers\Admin;

use App\Events\userActivation;
use App\Exports\CheckExport;
use App\Helpers\Helper;
use App\Http\Resources\CheckCollection;
use App\Models\Check;
use App\Models\Factor;
use App\Models\File;
use App\Models\TotalFactorItem;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;


class CheckController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!auth()->user()->isAbleTo('list-check')) {
            return abort(403);
        }
         $can_update_status_check = auth()->user()->isAbleTo('update-status-check');
        return view('Admin.check.all', compact('can_update_status_check'));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function filter(Request $request)
    {
        $checks = Check::filter()->orderBy('date', 'desc');
        if (isset($request->excel) && $request->excel == 'on') {
            return (new CheckExport($checks))->download('checks.xlsx');
        }
        return new CheckCollection($checks, $checks->paginate(20));
    }


    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Check $check
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Check $check)
    {
        if (!auth()->user()->isAbleTo('update-check') ) {
            return response(['status' => 'error', 'messages' => array('شما مجوز لازم جهت ویرایش چک ندارید .')]);
        }elseif ( $check->checkable->confirm == 1) {
            return response(['status' => 'error', 'messages' => array('امکان ویرایش چک های پرونده های تایید شده وجود ندارد .')]);
        }
        //validate
        $validator = $this->validateUpdateCheck($request);

        if ($validator->fails()) {
            return response(['status' => 'error', 'messages' => $validator->messages()->all()]);
        }
        //fix request
        $request->merge([
            'amount' => $request->amount ? ( float )str_replace(',', '', $request->amount) : 0,
            'date' => Helper::vertaToMiladi($request->date)
        ]);
        //update
        $this->updateCheckDetail($check, $request);
        //update history
        $this->updateCheckHistory($check, $request);
        //upload check images
        $this->uploadCheckImages($request, $check);
        return response(['status' => 'success', 'data' => $check]);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Check $check
     * @return \Illuminate\Http\Response
     */
    public function destroy(Check $check)
    {
        if (!auth()->user()->isAbleTo('delete-check')) {
            return response(['status' => 'error', 'messages' => array('شما مجوز لازم جهت حذف چک ندارید .')]);
        }elseif ($check->checkable->confirm == 1){
            return response(['status' => 'error', 'messages' => array('امکان حذف چک پرونده های تایید شده وجود ندارد .')]);
        }

        $check->delete();
        return response(['status' => 'success']);
    }


    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return array
     *
     */
    public function store(Request $request)
    {
        if (!auth()->user()->isAbleTo('store-check')) {
            return response(['status' => 'error', 'messages' => array('شما مجوز لازم جهت ایجاد چک را ندارید .')]);
        }
        //validate
        $validator = $this->validateStoreCheck($request);

        if ($validator->fails()) {
            return response(['status' => 'error', 'messages' => $validator->messages()->all()]);
        }

//        if (TotalFactorItem::find($request->total_factor_item_id)->confirm == 1) {
//            return abort(403);
//        }

        //find exporter and payer user
        is_null($request->exporter_id) ? $exporter = $this->createUser($request->exporter_phone, $request->exporter_name, $request->exporter_family, $request->exporter_province_id, $request->exporter_city_id, $request->exporter_gender) : $exporter = User::find($request->exporter_id);
        is_null($request->pay_to_id) ? $pay_to = $this->createUser($request->pay_to_phone, $request->pay_to_name, $request->pay_to_family, $request->pay_to_province_id, $request->exporter_city_id, $request->pay_to_gender) : $pay_to = User::find($request->pay_to_id);

        $applicant = User::find($request->applicant_id);

        //find case: |is factorItem or totalFactorItem|
        $total_factor_item = TotalFactorItem::find($request->total_factor_item_id);

        //fix request
        $request->merge([
            'amount' => $request->amount ? ( float )str_replace(',', '', $request->amount) : 0,
            'date' => Helper::vertaToMiladi($request->date)
        ]);
        //store
        $check = $this->storeCheck($request, $exporter, $applicant, $pay_to, $total_factor_item);

        //store history
        $this->storeCheckHistory($check, $request);

        //upload images
        if ($request->file('check_front_image')) {
            $this->uploadCheckFrontImage($check, $request->file('check_front_image'));
        }
        if ($request->file('check_back_image')) {
            $this->uploadCheckBackImage($check, $request->file('check_back_image'));
        }
        //return
        return response(['status' => 'success', 'data' => $check]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Foundation\Application|\Illuminate\Http\Response
     * sample request
     *  "check_id" => "11"
     * "status" => "11"
     * "receiver_phone" => "09858595896"
     * "receiver_name" => "علی"
     * "receiver_id" => "31550"
     * "receiver_family" => "علیزاده"
     */
    public function updateStatus(Request $request)
    {
        if (!auth()->user()->isAbleTo('update-status-check')) {
            return response(['status' => 'error', 'messages' => array('شما مجوز لازم جهت ویرایش وضعیت چک را ندارید .')]);
        }

        //validate
        $error_messages = $this->validateUpdateStatus($request);
        if (count($error_messages) > 0) {
            return response(['status' => 'error', 'messages' => $error_messages]);
        }
        $check = Check::find($request->check_id);

        switch ($request->status) {
            case 1:
                $this->receiptCheck($check , $request->payment_date);
                break;
            case 2:
                $this->bouncedCheck($check);
                break;
            case 3:
                $this->returnCheck($check);
                break;
            case 11:
                $receiver = $this->getOrCreateReceiver($request);
                $this->assignmentCheck($check, $receiver);
                break;
        }

        return response(['status' => 'success', 'messages' => ['عملیات موفق']]);
    }

    /**
     * upload front image of check
     * @param Check $check
     * @param $file
     * @return mixed
     */
    private function uploadCheckFrontImage(Check $check, $file)
    {
        $url_front_image = Helper::uploadImage($file, 'checks');
        return $check->files()->create(['path' => $url_front_image, 'title' => 'front']);
    }

    /**
     * upload back image of check
     * @param Check $check
     * @param $file
     * @return mixed
     */
    private function uploadCheckBackImage(Check $check, $file)
    {
        $url_back_image = Helper::uploadImage($file, 'checks');
        return $check->files()->create(['path' => $url_back_image, 'title' => 'back']);
    }

    /**
     * @param Request $request
     * @return array
     */
    private function validateUpdateStatus(Request $request): array
    {
        $request['receiver_phone'] = Helper::numberConverter($request->receiver_phone);

        $validator = Validator::make($request->all(), [
            'check_id' => ['required', 'exists:checks,id'],
            'status' => ['required', Rule::in(array(0, 1, 2, 3, 11))],
            'receiver_id' => 'nullable|exists:users,id',
            'receiver_phone' => 'nullable','regex:/(09)[0-9]{9}/|digits:11',
            'receiver_name' => 'nullable','string',
            'receiver_family' => 'nullable','string',
            'receiver_gender' => ['nullable', Rule::in(array(0, 1))],
        ]);

        $error_messages = array();
        if ($validator->fails()) {
            return $validator->messages()->all();
        }

        if (is_null($request->receiver_id) && $request->status == 11 && (is_null($request->phone)||is_null($request->name)||is_null($request->family)||is_null($request->gender)) ) {
            $error_messages[] = ' تمام اطلاعات کاربر الزامی می باشد , لطفا اطلاعات تحویل گیرنده را کامل وارد نمایید .';
        }

        $check = Check::find($request->check_id);
        if (in_array($request->status, array(1, 2)) && $check->date > Carbon::tomorrow()) {
            $error_messages[] = 'هنوز زمان وصول چک نرسیده است .';
        }
        if ($request->status == 3 && $check->title != 'guaranty') {
            $error_messages[] = 'عودت چک تنها شامل چک های ضمانت میباشد.';
        }
        if ($request->status == 2 && $check->status == 2) {
            $error_messages[] = 'این وضعیت قبلا ثبت شده است.';
        }
        if (in_array($request->status, array(2, 3, 1)) && $check->status == 1) {
            $error_messages[] = 'درخواست نامعتبر است  "این چک قبلا وصول شده است "';
        }
        if ($request->status == 4 && $check->title == 'guaranty') {
            $error_messages[] = 'خرج نمودن چک ، شامل چک های ضمانت نمی باشد.';
        }

        return $error_messages;
    }

    /**
     * @param Check $check
     * @param mixed $payment_date
     */
    private function receiptCheck(Check $check , $payment_date)
    {
        $check->update([
            'status' => 1
        ]);
        if ($check->title == 'installment' ||$check->title == 'prepayment'  ) {
            //crated factor
            $factor = new Factor();
            $factor->title = $check->title;
            $factor->user_id = $check->checkable->factor->user->id;
            $factor->price = $check->amount;
//            $factor->level =0;
            $factor->type = 'check';
            $factor->total_factor_item_id = $check->checkable->id;
            $factor->status = 1;
            $factor->expired_at = now();
            $factor->created_at = Helper::vertaToMiladi($payment_date);
            $factor->save();
            Factor::savePayment($factor);
        }

    }

    /**
     * @param Check $check
     */
    private function bouncedCheck(Check $check)
    {
        $check->update([
            'status' => 2
        ]);
    }

    /**
     * @param Check $check
     */
    private function returnCheck(Check $check)
    {
        $check->update([
            'status' => 3
        ]);

    }

    /**
     * @param Request $request
     * @return \Illuminate\Validation\Validator
     */
    private function validateStoreCheck(Request $request): \Illuminate\Validation\Validator
    {
        $request['exporter_phone'] = Helper::numberConverter($request->exporter_phone);
        $request['pay_to_phone'] = Helper::numberConverter($request->pay_to_phone);

        return Validator::make($request->all(), [
            'applicant_id' => 'nullable|numeric|exists:users,id',
            'title' => ['required',Rule::in(array_keys(Check::TITLE))],
            'exporter_id' => 'nullable|numeric|exists:users,id',
            'exporter_phone' => 'required_if:exporter_id,null|regex:/(09)[0-9]{9}/|digits:11',
            'exporter_name' => 'required_if:exporter_id,null|string',
            'exporter_family' => 'required_if:exporter_id,null|string',
            'exporter_gender' => ['required',Rule::in(array(0,1))],
            'exporter_province_id' => 'required_if:exporter_id,null|numeric|exists:provinces,id',
            'exporter_city_id' => 'required_if:exporter_id,null|numeric|exists:cities,id',
            'pay_to_id' => 'nullable|numeric|exists:users,id',
            'pay_to_phone' => 'required_if:pay_to_id,null|regex:/(09)[0-9]{9}/|digits:11',
            'pay_to_name' => 'required_if:pay_to_id,null|string',
            'pay_to_family' => 'required_if:pay_to_id,null|string',
            'pay_to_gender' =>['required',Rule::in(array(0,1))],
            'pay_to_province_id' => 'required_if:pay_to_id,null|numeric|exists:provinces,id',
            'pay_to_city' => 'required_if:pay_to_id,null|numeric|exists:cities,id',
            'amount' => ['required'],
            'date' => ['required'],
            'serial_code' => ['required'],
            'description' => ['nullable'],
            'total_factor_item_id' => ['required', 'integer', 'exists:total_factor_items,id'],
            'check_front_image' => ['required', 'mimes:png,jpg,jpeg'],
            'check_back_image' => ['mimes:png,jpg,jpeg']
        ]);
    }

    /**
     * @param Check $check
     * @param string $description
     */
    private function storeCheckActionHistory(Check $check, string $description)
    {
        $auth_user = auth()->user();
        $auth_user_full_name = $auth_user->name . ' ' . $auth_user->family;
        $check->histories()->create([
            'description' => $description . " $auth_user_full_name _" . $auth_user->id
        ]);
    }

    /**
     * @param $request
     * @return mixed
     */
    private function createUser($phone, $name, $family, $province_id, $city_id,$gender)
    {
        $pass = rand(12345, 99999);
        $phone = Helper::numberConverter($phone);

        return User::create([
            'name' => $name,
            'family' => $family,
            'phone' => $phone,
            'sex' => $gender,
            'password' => Hash::make($pass),
            'active' => true,
            'level' => 'user',
            'province_id ' => $province_id,
            'city_id' => $city_id
        ]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Validation\Validator
     */
    private function validateUpdateCheck(Request $request): \Illuminate\Validation\Validator
    {
        return Validator::make($request->all(), [
            'amount' => ['required'],
            'date' => ['required'],
            'description' => ['nullable'],
            'serial_code' => ['required'],
            'check_front_image' => ['mimes:png,jpg,jpeg'],
            'check_back_image' => ['mimes:png,jpg,jpeg']
        ]);
    }


    /**
     * @param Check $check
     * @param User $receiver
     * @return \Illuminate\Database\Eloquent\Model
     */
    private function assignmentCheck(Check $check, User $receiver)
    {
        return $check->assetTransactions()->create([
            'receiverable_id' => $receiver->id,
            'receiverable_type' => get_class($receiver),
            'created_by' => auth()->user()->id
        ]);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    private function getOrCreateReceiver(Request $request)
    {
       if (isset($request->receiver_id)){
           $receiver=User::find($request->receiver_id);
       }else{
           $receiver=$this->createUser($request->receiver_phone,$request->receiver_name,$request->receiver_family,null,null,$request->receiver_gender);
       }
       return $receiver;
    }

    public function repair()
    {
        $checks = Check::all();
        $user_receiver= User::find(63);
        foreach ($checks as $check){
            $check_owner = $check->checkable->factor->user;
            if (is_null($check->applicantable_id)){
                $check->update([
                    'applicantable_id'=>$check_owner->id,
                    'applicantable_type'=>get_class($check_owner)
                ]);
            }
            if (is_null($check->exportable_id)){
                $check->update([
                    'exportable_id'=>$check_owner->id,
                    'exportable_type'=>get_class($check_owner)
                ]);
            }
            if (is_null($check->paytoable_id )){
                $check->update([
                    'paytoable_id'=>$user_receiver->id,
                    'paytoable_type'=>get_class($user_receiver)
                ]);
            }
        }
        return Check::all();
    }

    /**
     * @param Request $request
     * @param mixed $exporter
     * @param $applicant
     * @param mixed $pay_to
     * @param $total_factor_item
     * @return mixed
     */
    private function storeCheck(Request $request, mixed $exporter, $applicant, mixed $pay_to, $total_factor_item)
    {
        $check = Check::create([
            'amount' => $request->amount,
            'creator_id' => auth()->user()->id,
            'exportable_id' => $exporter->id,
            'exportable_type' => $exporter::class,
            'applicantable_id' => $applicant->id,
            'applicantable_type' => $applicant::class,
            'paytoable_id' => $pay_to->id,
            'paytoable_type' => $pay_to::class,
            'serial_code' => $request->serial_code,
            'date' => $request->date,
            'title' => $request->title,
            'status' => $request->title == 'guaranty' ? 4 : 5,
            'checkable_id' => $total_factor_item->id,
            'checkable_type' => get_class($total_factor_item)
        ]);
        return $check;
    }

    /**
     * @param mixed $check
     * @param Request $request
     */
    private function storeCheckHistory(mixed $check, Request $request): void
    {
        $check->histories()->create([
            'description' => $request->description,
            'user_id' => auth()->user()->id
        ]);
    }

    /**
     * @param Check $check
     * @param Request $request
     */
    private function updateCheckDetail(Check $check, Request $request): void
    {
        $check->update([
            'amount' => $request->amount,
            'serial_code' => $request->serial_code,
            'date' => $request->date,
            'title' => $request->title,
        ]);
    }

    /**
     * @param Check $check
     * @param Request $request
     */
    private function updateCheckHistory(Check $check, Request $request): void
    {
        if ($check->histories()->exists() && $request->description != $check->histories->last()->description)
            $check->histories()->create([
                'description' => $request->description,
            ]);
    }

    /**
     * @param Request $request
     * @param Check $check
     */
    private function uploadCheckImages(Request $request, Check $check): void
    {
        if ($request->file('check_front_image')) {
            $front_image = File::where('title', 'front')->where('fileable_type', Check::class)->where('fileable_id', $check->id)->first();

            if (!is_null($front_image)) {
                Helper::deleteImage(public_path($front_image->path));
                $front_image->delete();
            }
            $this->uploadCheckFrontImage($check, $request->file('check_front_image'));

        }
        if ($request->file('check_back_image')) {
            $back_image = File::where('title', 'back')->where('fileable_type', Check::class)->where('fileable_id', $check->id)->first();
            if (!is_null($back_image)) {
                Helper::deleteImage(public_path($back_image->path));
                $back_image->delete();
            }
            $this->uploadCheckBackImage($check, $request->file('check_back_image'));
        }
    }
}
