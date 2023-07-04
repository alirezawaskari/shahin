<?php

namespace App\Http\Controllers\Admin;

use App\Exports\CheckExport;
use App\Helpers\Helper;
use App\Http\Resources\CheckCollection;
use App\Models\Check;
use App\Models\Factor;
use App\Models\File;
use App\Models\TotalFactorItem;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
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
        $can_update_status_check  = auth()->user()->isAbleTo( 'update-status-check' );
        return view('Admin.check.all',compact('can_update_status_check'));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function filter(Request $request)
    {
        $checks=  Check::filter()->orderBy('date', 'desc');
        if (isset($request->excel) && $request->excel == 'on') {
           return (new CheckExport($checks))->download('checks.xlsx');
        }
        return new CheckCollection( $checks , $checks->paginate( 20 ) );
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
        if (!auth()->user()->isAbleTo('update-check') || $check->checkable->confirm == 1) {
            return abort(403);
        }
        //validate
        $validator = Validator::make($request->all(), [
            'amount' => ['required'],
            'date' => ['required'],
            'description' => ['nullable'],
            'exporter' => ['required'],
            'serial_code' => ['required'],
            'pay_to' => ['required'],
            'check_front_image' => ['mimes:png,jpg,jpeg'],
            'check_back_image' => ['mimes:png,jpg,jpeg']
        ]);

        if ($validator->fails()) {
            return response(['status' => 'error', 'messages' => $validator->messages()->all()]);
        }
        //fix request
        $request->merge([
            'amount' => $request->amount ? ( float )str_replace(',', '', $request->amount) : 0,
            'date' => Helper::vertaToMiladi($request->date)
        ]);
        //update
        $check->update([
            'amount' => $request->amount,
            'creator_id' => auth()->user()->id,
            'exporter' => $request->exporter,
            'pay_to' => $request->pay_to,
            'serial_code' => $request->serial_code,
            'date' => $request->date,
            'title' => $request->title,
        ]);
        //update history
        if ($check->histories()->exists() && $request->description != $check->histories->last()->description)
            $check->histories()->create([
                'description' => $request->description,
            ]);
        //upload check images
        if ($request->file('check_front_image')) {
           $front_image=File::where('title','front')->where('fileable_type',Check::class)->where('fileable_id',$check->id)->first();

            if (!is_null($front_image)) {
                Helper::deleteImage(public_path($front_image->path));
                $front_image->delete();
            }
            $this->uploadCheckFrontImage($check, $request->file('check_front_image'));

        }
        if ($request->file('check_back_image')) {
            $back_image=File::where('title','back')->where('fileable_type',Check::class)->where('fileable_id',$check->id)->first();
            if (!is_null($back_image)) {
                Helper::deleteImage(public_path($back_image->path));
                $back_image->delete();
            }
            $this->uploadCheckBackImage($check, $request->file('check_back_image'));
        }
        return response(['status' => 'success', 'data' =>  $check ]);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Check $check
     * @return \Illuminate\Http\Response
     */
    public function destroy(Check $check)
    {
        if (!auth()->user()->isAbleTo('delete-check') || $check->checkable->confirm == 1) {
            return abort(403);
        }

        $auth_user=auth()->user();
//        $auth_user_full_name=$auth_user->name.' '.$auth_user->family;
//        $check->histories()->create([
//            'description'=>"  چک حذف گردید ، شناسه و نام کاربر حذف کننده :"." $auth_user_full_name _".$auth_user->id
//        ]);
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
            return abort(403);
        }
        //validate
        $validator = Validator::make($request->all(), [
            'amount' => ['required'],
            'date' => ['required'],
            'serial_code' => ['required'],
            'description' => ['nullable'],
            'exporter' => ['required'],
            'pay_to' => ['required'],
            'total_factor_item_id' => ['required', 'integer','exists:total_factor_items,id'],
            'check_front_image' => ['required', 'mimes:png,jpg,jpeg'],
            'check_back_image' => ['mimes:png,jpg,jpeg']
        ]);

        if ($validator->fails()) {
            return response(['status' => 'error', 'messages' => $validator->messages()->all()]);
        }

        if (TotalFactorItem::find($request->total_factor_item_id)->confirm == 1) {
            return abort(403);
        }
        //find case: |is factorItem or totalFactorItem|

        $total_factor_item = TotalFactorItem::find($request->total_factor_item_id);

        //fix request
        $request->merge([
            'amount' => $request->amount ? ( float )str_replace(',', '', $request->amount) : 0,
            'date' => Helper::vertaToMiladi($request->date)
        ]);
        //store
        $check = Check::create([
            'amount' => $request->amount,
            'creator_id' => auth()->user()->id,
            'exporter' => $request->exporter,
            'pay_to' => $request->pay_to,
            'serial_code' => $request->serial_code,
            'date' => $request->date,
            'title' => $request->title,
            'status' => 0,
            'checkable_id' => $total_factor_item->id,
            'checkable_type' => get_class($total_factor_item)
        ]);
        //store history
        $check->histories()->create([
            'description' => $request->description,
            'user_id' => auth()->user()->id
        ]);

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
     */
    public function updateStatus(Request $request)
    {
        if (!auth()->user()->isAbleTo('update-status-check')) {
            return abort(403);
        }
        //validate
        $error_messages=$this->validateUpdateStatus($request);
        if (count($error_messages)>0) {
            return response(['status' => 'error', 'messages' => $error_messages]);
        }
        $check=Check::find($request->check_id);

        switch ($request->status) {
            case 1:
                $this->receiptCheck($check);
                break;
            case 2:
                $this->bouncedCheck($check);
                break;
            case 3:
                $this->returnCheck($check);
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
    private function validateUpdateStatus(Request $request):array
    {
        $validator = Validator::make($request->all(), [
            'check_id' => ['required', 'exists:checks,id'],
            'status' => ['required',Rule::in(array(0,1,2,3))],
        ]);

        $error_messages=array();
        if ($validator->fails()) {
            return $validator->messages()->all();
        }

        $check=Check::find($request->check_id);
        if (in_array($request->status,array(1,2)) && $check->date > Carbon::tomorrow()){
            $error_messages[]='هنوز زمان وصول چک نرسیده است .';
        }

        if ($request->status == 3 && $check->title!= 'guaranty'){
            $error_messages[]='عودت چک تنها شامل چک های ضمانت میباشد.';
        }
        if ($request->status == 2 && $check->status== 2 ){
            $error_messages[]='این وضعیت قبلا ثبت شده است.';
        }
        if (in_array($request->status,array(2,3,1))    && $check->status == 1){
            $error_messages[]='درخواست نامعتبر است  "این چک قبلا وصول شده است "';
        }

        return $error_messages;
    }

    /**
     * @param Check $check
     */
    private function receiptCheck(Check $check)
    {
        $check->update([
            'status'=>1
        ]);
        if ($check->title == 'installment'){
            //crated factor
            $factor = new Factor();
            $factor->title = 'installment';
            $factor->user_id = $check->checkable->factor->user->id;
            $factor->price = $check->amount;
//            $factor->level =0;
            $factor->type = 'check';
            $factor->total_factor_item_id = $check->checkable->id;
            $factor->status = 1;
            $factor->expired_at = now();
            $factor->save();
            Factor::savePayment($factor);
        }
//        $history_text=" چک وصول شد ، شناسه و نام کاربر ثبت کننده وصول چک :";
//        $this->storeCheckActionHistory($check,$history_text);
    }

    /**
     * @param Check $check
     */
    private function bouncedCheck(Check $check)
    {
        $check->update([
           'status'=>2
        ]);
//        $history_text=" چک برگشت خورد ، شناسه و نام کاربر ثبت کننده برگشت :";
//        $this->storeCheckActionHistory($check,$history_text);
    }

    /**
     * @param Check $check
     */
    private function returnCheck(Check $check)
    {
        $check->update([
            'status'=>3
        ]);
//        $history_text="  چک عودت داده شد ، شناسه و نام کاربر ثبت کننده عودت:";
//        $this->storeCheckActionHistory($check,$history_text);
    }

    /**
     * @param Check $check
     * @param string $description
     */
    private function storeCheckActionHistory(Check $check, string $description){
        $auth_user=auth()->user();
        $auth_user_full_name=$auth_user->name.' '.$auth_user->family;
        $check->histories()->create([
            'description'=>$description." $auth_user_full_name _".$auth_user->id
        ]);
    }
}
