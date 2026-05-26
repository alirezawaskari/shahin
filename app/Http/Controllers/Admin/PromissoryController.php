<?php

namespace App\Http\Controllers\Admin;

use App\Exports\PromissoryExport;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Resources\PromissoryCollection;
use App\Models\File;
use App\Models\Promissory;
use App\Models\TotalFactorItem;
use App\Models\User;
use Illuminate\Http\Request;
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

        if (!auth()->user()->isAbleTo('update-promissory') ) {
            return response(['status' => 'error', 'messages' => array('شما مجوز لازم جهت ویرایش سفته ندارید .')]);
        }else if ( $promissory->promissoryable->confirm == 1) {
            return response(['status' => 'error', 'messages' => array('امکان ویرایش سفته های پرونده های تایید شده وجود ندارد .')]);
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
        if (!auth()->user()->isAbleTo('delete-promissory')) {
            return response(['status' => 'error', 'messages' => array('شما مجوز لازم جهت حذف سفته ندارید .')]);
        }elseif ($promissory->promissoryable->confirm == 1){
            return response(['status' => 'error', 'messages' => array('امکان حذف سفته پرونده های تایید شده وجود ندارد .')]);
        }

        $promissory->delete();
        return response(['status' => 'success']);
    }


    /**
     * Summary of store
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (!auth()->user()->isAbleTo('store-promissory') ) {
            return response(['status' => 'error', 'messages' => array('شما مجوز لازم جهت ذخیره سفته ندارید .')]);
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
        is_null($request->exporter_id) ? $exporter = $this->createUser($request , $request->exporter_phone, $request->exporter_name, $request->exporter_family, $request->exporter_province_id, $request->exporter_city_id, $request->exporter_gender) : $exporter = User::find($request->exporter_id);
        is_null($request->pay_to_id) ? $pay_to = $this->createUser($request , $request->pay_to_phone, $request->pay_to_name, $request->pay_to_family, $request->pay_to_province_id, $request->pay_to_city_id, $request->pay_to_gender) : $pay_to = User::find($request->pay_to_id);
        is_null($request->guaranty_id) ? $guarantor = $this->createUser($request , $request->guaranty_phone, $request->guaranty_name, $request->guaranty_family, $request->guaranty_province_id, $request->guaranty_city_id, $request->guaranty_gender) : $guarantor = User::find($request->guaranty_id);
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
            'promissoryable_type' => get_class($total_factor_item),
            'representation_id' => auth()->user()->admin_representation_id,
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
            return response(['status' => 'error', 'messages' => array('شما مجوز لازم جهت ویرایش وضعیت سفته را ندارید .')]);
        }

        //validate
        $error_messages = $this->validateUpdateStatus($request);
        if (count($error_messages) > 0) {
            return response(['status' => 'error', 'messages' => $error_messages]);
        }
        $promissory = Promissory::find($request->promissory_id);

        switch ($request->status) {
            case 0:
                $this->archivePromissory($promissory);
                break;
            case 1:
                $this->returnPromissory($promissory);
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
            'status' => ['required', Rule::in(array(0, 1))],
        ]);

        $error_messages = array();
        if ($validator->fails()) {
            return $validator->messages()->all();
        }

        $promissory = Promissory::find($request->promissory_id);

        if ($request->status == 1 && $promissory->status == 1) {
            $error_messages[] = 'درخواست نامعتبر ، عودت قبلا ثبت شده است .';
        }
        if ($request->status == 0 && $promissory->status == 0) {
            $error_messages[] = 'درخواست نامعتبر ، رسوب در خزانه قبلا ثبت شده است .';
        }

        return $error_messages;
    }

    /**
     * @param Promissory $promissory
     */
    private function returnPromissory(Promissory $promissory)
    {
        $promissory->update([
            'status' => 1
        ]);

    }
    /**
     * @param Promissory $promissory
     */
    private function archivePromissory(Promissory $promissory)
    {
        $promissory->update([
            'status' => 0
        ]);

    }

    /**
     * @param Request $request
     * @return \Illuminate\Validation\Validator
     */
    private function validateStorePromissory(Request $request): \Illuminate\Validation\Validator
    {
        $request['exporter_phone'] = Helper::numberConverter($request->exporter_phone);
        $request['pay_to_phone'] = Helper::numberConverter($request->pay_to_phone);
        $request['guaranty_phone'] = Helper::numberConverter($request->guaranty_phone);

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
            'pay_to_city_id' => 'required_if:pay_to_id,null|numeric|exists:cities,id',
            'guaranty_id' => 'nullable|numeric|exists:users,id',
            'guaranty_phone' => 'required_if:guaranty_id,null|regex:/(09)[0-9]{9}/|digits:11',
            'guaranty_name' => 'required_if:guaranty_id,null|string',
            'guaranty_family' => 'required_if:guaranty_id,null|string',
            'guaranty_gender' =>['required',Rule::in(array(0,1))],
            'guaranty_province_id' => 'required_if:guaranty_id,null|numeric|exists:provinces,id',
            'guaranty_city_id' => 'required_if:guaranty_id,null|numeric|exists:cities,id',
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
     * Summary of createUser
     * @param mixed $request
     * @param mixed $phone
     * @param mixed $name
     * @param mixed $family
     * @param mixed $province
     * @param mixed $city
     * @param mixed $gender
     * @return User
     */
    private function createUser($request , $phone, $name, $family, $province, $city,$gender)
    {
        $request->phone = $phone;
        $request->name = $name;
        $request->family = $family;
        $request->province = $province;
        $request->city = $city;
        $request->gender = $gender;
        User::createUser($request);
        return User::where('phone',$request->phone)->first();
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

}
