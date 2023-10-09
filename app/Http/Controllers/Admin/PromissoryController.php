<?php

namespace App\Http\Controllers\Admin;

use App\Events\userActivation;
use App\Exports\PromissoryExport;
use App\Helpers\Helper;
use App\Http\Resources\PromissoryCollection;
use App\Models\Promissory;
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


class PromissoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!auth()->user()->isAbleTo('list-promissory')) {
            return abort(403);
        }
         $can_update_status_promissory = auth()->user()->isAbleTo('update-status-promissory');
        return view('Admin.promissory.all', compact('can_update_status_promissory'));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function filter(Request $request)
    {
        $promissories = Promissory::filter()->latest();

        if (isset($request->excel) && $request->excel == 'on') {
            return (new PromissoryExport($promissories))->download('promissories.xlsx');
        }
        return new PromissoryCollection($promissories, $promissories->paginate(20));
    }


    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Promissory $promissory
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Promissory $promissory)
    {
        if (!auth()->user()->isAbleTo('update-promissory') || $promissory->promissoryable->confirm == 1) {
            return abort(403);
        }
        //validate
        $validator = $this->validateUpdatePromissory($request);

        if ($validator->fails()) {
            return response(['status' => 'error', 'messages' => $validator->messages()->all()]);
        }
        //fix request
        $request->merge([
            'amount' => $request->amount ? ( float )str_replace(',', '', $request->amount) : 0,
            'date' => Helper::vertaToMiladi($request->date)
        ]);
        //update
        $promissory->update([
            'amount' => $request->amount,
            'serial_code' => $request->serial_code,
            'date' => $request->date,
            'title' => $request->title,
        ]);
        //update history
        if ($promissory->histories()->exists() && $request->description != $promissory->histories->last()->description)
            $promissory->histories()->create([
                'description' => $request->description,
            ]);
        //upload promissory images
        if ($request->file('promissory_front_image')) {
            $front_image = File::where('title', 'front')->where('fileable_type', Promissory::class)->where('fileable_id', $promissory->id)->first();

            if (!is_null($front_image)) {
                Helper::deleteImage(public_path($front_image->path));
                $front_image->delete();
            }
            $this->uploadPromissoryFrontImage($promissory, $request->file('promissory_front_image'));

        }
        if ($request->file('promissory_back_image')) {
            $back_image = File::where('title', 'back')->where('fileable_type', Promissory::class)->where('fileable_id', $promissory->id)->first();
            if (!is_null($back_image)) {
                Helper::deleteImage(public_path($back_image->path));
                $back_image->delete();
            }
            $this->uploadPromissoryBackImage($promissory, $request->file('promissory_back_image'));
        }
        return response(['status' => 'success', 'data' => $promissory]);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Promissory $promissory
     * @return \Illuminate\Http\Response
     */
    public function destroy(Promissory $promissory)
    {
        if (!auth()->user()->isAbleTo('delete-promissory') || $promissory->promissoryable->confirm == 1) {
            return abort(403);
        }
        $promissory->delete();
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
        if (!auth()->user()->isAbleTo('store-promissory')) {
            return abort(403);
        }
        //validate
        $validator = $this->validateStorePromissory($request);

        if ($validator->fails()) {
            return response(['status' => 'error', 'messages' => $validator->messages()->all()]);
        }

//        if (TotalFactorItem::find($request->total_factor_item_id)->confirm == 1) {
//            return abort(403);
//        }

        //find exporter and payer user
        is_null($request->exporter_id) ? $exporter = $this->createUser($request->exporter_phone, $request->exporter_name, $request->exporter_family, $request->exporter_province_id, $request->exporter_city_id, $request->exporter_gender) : $exporter = User::find($request->exporter_id);
        is_null($request->pay_to_id) ? $pay_to = $this->createUser($request->pay_to_phone, $request->pay_to_name, $request->pay_to_family, $request->pay_to_province_id, $request->exporter_city_id, $request->pay_to_gender) : $pay_to = User::find($request->pay_to_id);
        is_null($request->guaranty_id) ? $guarantor = $this->createUser($request->guaranty_phone, $request->guaranty_name, $request->guaranty_family, $request->guaranty_province_id, $request->guaranty_city_id, $request->guaranty_gender) : $guarantor = User::find($request->guaranty_id);

        $applicant = User::find($request->applicant_id);

        //find case: |is factorItem or totalFactorItem|
        $total_factor_item = TotalFactorItem::find($request->total_factor_item_id);

        //fix request
        $request->merge([
            'amount' => $request->amount ? ( float )str_replace(',', '', $request->amount) : 0,
            'date' => Helper::vertaToMiladi($request->date)
        ]);
        //store

        $promissory = Promissory::create([
            'amount' => $request->amount,
            'creator_id' => auth()->user()->id,
            'exportable_id' => $exporter->id,
            'exportable_type' => $exporter::class,
            'applicantable_id' => $applicant->id,
            'applicantable_type' => $applicant::class,
            'paytoable_id' => $pay_to->id,
            'paytoable_type' => $pay_to::class,
            'guarantorable_id' => $guarantor->id,
            'guarantorable_type' => $guarantor::class,
            'serial_code' => $request->serial_code,
            'date' => $request->date,
            'title' => $request->title,
            'status' => 0,
            'promissoryable_id' => $total_factor_item->id,
            'promissoryable_type' => get_class($total_factor_item)
        ]);
        //store history
        $promissory->histories()->create([
            'description' => $request->description,
            'user_id' => auth()->user()->id
        ]);

        //upload images
        if ($request->file('promissory_front_image')) {
            $this->uploadPromissoryFrontImage($promissory, $request->file('promissory_front_image'));
        }
        if ($request->file('promissory_back_image')) {
            $this->uploadPromissoryBackImage($promissory, $request->file('promissory_back_image'));
        }
        //return
        return response(['status' => 'success', 'data' => $promissory]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Foundation\Application|\Illuminate\Http\Response
     * sample request
     *  "promissory_id" => "11"
     * "status" => "11"
     * "receiver_phone" => "09858595896"
     * "receiver_name" => "علی"
     * "receiver_id" => "31550"
     * "receiver_family" => "علیزاده"
     */
    public function updateStatus(Request $request)
    {
        if (!auth()->user()->isAbleTo('update-status-promissory')) {
            return abort(403);
        }

        //validate
        $error_messages = $this->validateUpdateStatus($request);
        if (count($error_messages) > 0) {
            return response(['status' => 'error', 'messages' => $error_messages]);
        }
        $promissory = Promissory::find($request->promissory_id);

        switch ($request->status) {
            case 1:
                $this->receiptPromissory($promissory);
                break;
            case 2:
                $this->bouncedPromissory($promissory);
                break;
            case 3:
                $this->returnPromissory($promissory);
                break;
            case 11:
                $receiver = $this->getOrCreateReceiver($request);
                $this->assignmentPromissory($promissory, $receiver);
                break;
        }

        return response(['status' => 'success', 'messages' => ['عملیات موفق']]);
    }

    /**
     * upload front image of promissory
     * @param Promissory $promissory
     * @param $file
     * @return mixed
     */
    private function uploadPromissoryFrontImage(Promissory $promissory, $file)
    {
        $url_front_image = Helper::uploadImage($file, 'promissories');
        return $promissory->files()->create(['path' => $url_front_image, 'title' => 'front']);
    }

    /**
     * upload back image of promissory
     * @param Promissory $promissory
     * @param $file
     * @return mixed
     */
    private function uploadPromissoryBackImage(Promissory $promissory, $file)
    {
        $url_back_image = Helper::uploadImage($file, 'promissories');
        return $promissory->files()->create(['path' => $url_back_image, 'title' => 'back']);
    }

    /**
     * @param Request $request
     * @return array
     */
    private function validateUpdateStatus(Request $request): array
    {
        $validator = Validator::make($request->all(), [
            'promissory_id' => ['required', 'exists:promissories,id'],
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

        $promissory = Promissory::find($request->promissory_id);
        if (in_array($request->status, array(1, 2)) && $promissory->date > Carbon::tomorrow()) {
            $error_messages[] = 'هنوز زمان وصول چک نرسیده است .';
        }
        if ($request->status == 3 && $promissory->title != 'guaranty') {
            $error_messages[] = 'عودت چک تنها شامل چک های ضمانت میباشد.';
        }
        if ($request->status == 2 && $promissory->status == 2) {
            $error_messages[] = 'این وضعیت قبلا ثبت شده است.';
        }
        if (in_array($request->status, array(2, 3, 1)) && $promissory->status == 1) {
            $error_messages[] = 'درخواست نامعتبر است  "این چک قبلا وصول شده است "';
        }
        if ($request->status == 4 && $promissory->title == 'guaranty') {
            $error_messages[] = 'خرج نمودن چک ، شامل چک های ضمانت نمی باشد.';
        }

        return $error_messages;
    }

    /**
     * @param Promissory $promissory
     */
    private function receiptPromissory(Promissory $promissory)
    {
        $promissory->update([
            'status' => 1
        ]);
        if ($promissory->title == 'installment' ||$promissory->title == 'prepayment'  ) {
            //crated factor
            $factor = new Factor();
            $factor->title = $promissory->title;
            $factor->user_id = $promissory->promissoryable->factor->user->id;
            $factor->price = $promissory->amount;
//            $factor->level =0;
            $factor->type = 'promissory';
            $factor->total_factor_item_id = $promissory->promissoryable->id;
            $factor->status = 1;
            $factor->expired_at = now();
            $factor->save();
            Factor::savePayment($factor);
        }

    }

    /**
     * @param Promissory $promissory
     */
    private function bouncedPromissory(Promissory $promissory)
    {
        $promissory->update([
            'status' => 2
        ]);
    }

    /**
     * @param Promissory $promissory
     */
    private function returnPromissory(Promissory $promissory)
    {
        $promissory->update([
            'status' => 3
        ]);

    }

    /**
     * @param Request $request
     * @return \Illuminate\Validation\Validator
     */
    private function validateStorePromissory(Request $request): \Illuminate\Validation\Validator
    {
        return Validator::make($request->all(), [
            'applicant_id' => 'nullable|numeric|exists:users,id',
            'title' => ['required',Rule::in(array_keys(Promissory::TITLE))],
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
            'promissory_front_image' => ['required', 'mimes:png,jpg,jpeg'],
            'promissory_back_image' => ['mimes:png,jpg,jpeg']
        ]);
    }

    /**
     * @param Promissory $promissory
     * @param string $description
     */
    private function storePromissoryActionHistory(Promissory $promissory, string $description)
    {
        $auth_user = auth()->user();
        $auth_user_full_name = $auth_user->name . ' ' . $auth_user->family;
        $promissory->histories()->create([
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
    private function validateUpdatePromissory(Request $request): \Illuminate\Validation\Validator
    {
        return Validator::make($request->all(), [
            'amount' => ['required'],
            'date' => ['required'],
            'description' => ['nullable'],
            'serial_code' => ['required'],
            'promissory_front_image' => ['mimes:png,jpg,jpeg'],
            'promissory_back_image' => ['mimes:png,jpg,jpeg']
        ]);
    }


    /**
     * @param Promissory $promissory
     * @param User $receiver
     * @return \Illuminate\Database\Eloquent\Model
     */
    private function assignmentPromissory(Promissory $promissory, User $receiver)
    {
        return $promissory->assetTransactions()->create([
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
        $promissories = Promissory::all();
        $user_receiver= User::find(63);
        foreach ($promissories as $promissory){
            $promissory_owner = $promissory->promissoryable->factor->user;
            if (is_null($promissory->applicantable_id)){
                $promissory->update([
                    'applicantable_id'=>$promissory_owner->id,
                    'applicantable_type'=>get_class($promissory_owner)
                ]);
            }
            if (is_null($promissory->exportable_id)){
                $promissory->update([
                    'exportable_id'=>$promissory_owner->id,
                    'exportable_type'=>get_class($promissory_owner)
                ]);
            }
            if (is_null($promissory->paytoable_id )){
                $promissory->update([
                    'paytoable_id'=>$user_receiver->id,
                    'paytoable_type'=>get_class($user_receiver)
                ]);
            }
        }
        return Promissory::all();
    }
}
