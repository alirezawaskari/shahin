<?php

namespace App\Http\Controllers\Admin;

use App\Events\smsService;
use App\Exports\FactorsExport;
use App\Models\Factor;
use App\Http\Controllers\Controller;
use App\Http\Resources\FactorCollection;
use App\Models\User;
use Carbon\Carbon;
use App\Helpers\Helper;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Psy\Command\ListCommand\FunctionEnumerator;

class FactorController extends Controller
{
    public function index()
    {
        $labels = ['پرداخت موفق', 'پرداخت معلق'];
        $title = "all";
        $user = auth()->user();
        $organ = false;
        $wallets = 0;
        $credits = 0;
        if ($user->admin_organization_id){
            $wallets = $user->admin_organization->users->sum('wallet');
            $credits =  $user->admin_organization->users->sum('credit');
            $organ = true;
        }
        if ($user->admin_representation) {
            if ($user->hasPermission('factor-detail')) {
                $values = [
                    Factor::where('representation_id', $user->admin_representation->id)->whereStatus(true)->whereIn('type' , Factor::getCashPaymentType())->sum('price'),
                    Factor::where('representation_id', $user->admin_representation->id)->where('status' , '!=' , true)->whereIn('type' , Factor::getCashPaymentType())->sum('price')
                ];
                $day_values = [
                    Factor::where('representation_id', $user->admin_representation->id)->whereDate('created_at', Helper::vertaTocarbon(verta()->today()))->whereStatus(true)->whereIn('type' , Factor::getCashPaymentType())->sum('price'),
                    Factor::where('representation_id', $user->admin_representation->id)->whereDate('created_at', Helper::vertaTocarbon(verta()->today()))->where('status' , '!=' , true)->whereIn('type' , Factor::getCashPaymentType())->sum('price')
                ];

            } else {
                $values = [
                    Factor::where('representation_id', $user->admin_representation->id)->where('callcenter_id', $user->id)->whereStatus(true)->whereIn('type' , Factor::getCashPaymentType())->sum('price'),
                    Factor::where('representation_id', $user->admin_representation->id)->where('callcenter_id', $user->id)->where('status' , '!=' , true)->whereIn('type' , Factor::getCashPaymentType())->sum('price')
                ];
                $day_values = [
                    Factor::where('representation_id', $user->admin_representation->id)->where('callcenter_id', $user->id)->whereDate('created_at', Helper::vertaTocarbon(verta()->today()))->whereStatus(true)->whereIn('type' , Factor::getCashPaymentType())->sum('price'),
                    Factor::where('representation_id', $user->admin_representation->id)->where('callcenter_id', $user->id)->whereDate('created_at', Helper::vertaTocarbon(verta()->today()))->where('status' , '!=' , true)->whereIn('type' , Factor::getCashPaymentType())->sum('price')
                ];

            }
        } else {
            if ($user->hasPermission('factor-detail')) {

                $values = [
                    Factor::whereStatus(true)->sum('price'),
                    Factor::where('status' , '!=' , true)->sum('price')
                ];
                $day_values = [
                    Factor::whereDate('created_at', Helper::vertaTocarbon(verta()->today()))->whereStatus(true)->whereIn('type' , Factor::getCashPaymentType())->sum('price'),
                    Factor::whereDate('created_at', Helper::vertaTocarbon(verta()->today()))->where('status' , '!=' , true)->whereIn('type' , Factor::getCashPaymentType())->sum('price')
                ];

            } else {
                $values = [
                    Factor::whereStatus(true)->where('callcenter_id', $user->id)->sum('price'),
                    Factor::where('status' , '!=' , true)->where('callcenter_id', $user->id)->sum('price')
                ];
                $day_values = [
                    Factor::where('callcenter_id', $user->id)->whereDate('created_at', Helper::vertaTocarbon(verta()->today()))->whereStatus(true)->whereIn('type' , Factor::getCashPaymentType())->sum('price'),
                    Factor::where('callcenter_id', $user->id)->whereDate('created_at', Helper::vertaTocarbon(verta()->today()))->where('status' , '!=' , true)->whereIn('type' , Factor::getCashPaymentType())->sum('price')
                ];
            }
        }

        $detail = false;
        $delete = false;
        if ($user->isAbleTo('factor-detail')){
            $detail = true;
        }
        if ($user->isAbleTo('factor-delete')){
            $delete = true;
        }

        return view('Admin.factor.all' , compact('title' , 'delete' , 'detail' ,'wallets','credits', 'organ','labels' , 'day_values' , 'values'));
    }

    public function desktop()
    {
        $user = auth()->user();
        $desktop = true;
        $title = "desktop";

        $detail = false;
        if ($user->isAbleTo('factor-detail')){
            $detail = true;
        }

        return view('Admin.factor.desktop' , compact('title' , 'detail', 'desktop'));
    }

    public function filter(Request $request)
    {

        $factors = Factor::filter()->latest();

        if ($request->exel == 'on') {
            return (new FactorsExport($factors))->download('factors.xlsx');
        }
        return new FactorCollection($factors,$factors->paginate(20));
    }

    public function filterDesktop(Request $request)
    {
        $factors = Factor::where('status', 1)->where('confirm' , 0)->filter()->latest();

        if ($request->exel == 'on') {
            return (new FactorsExport($factors))->download('factors.xlsx');
        }
        return new FactorCollection($factors->get(),$factors->paginate(20));
    }

    public function create()
    {
        return view('Admin.factor.create');
    }

    public function edit(Factor $factor,Request $request)
    {
        $user = $factor->user;
        $type = $factor->factorable_type;
        $comments = Helper::userHistories($user);
        $changes = $factor->audits()->with('user')->get()->last();
        $items = $factor->factorItems->map(function ($item) {
            $temp = [];
            $temp['id'] = $item->id;
            $temp['service'] = $item->factoritemable;
            $temp['service_item'] = !is_null($item->service_item_id) ? $item->serviceItem : null;
            $temp['centers'] = $item->factoritemable->center == true ? $item->factoritemable->centers : null;
            $temp['complete'] = $item->complete;
            $temp['values'] = $item->values;
            $temp['factor_values'] = $item->factor_values;
            $temp['factor_image'] = $item->factor_image;
            $temp['status_factor'] = $item->status_factor;
            $temp['confirms'] = $item->confirms;
            $temp['center'] = $item->center;
            $temp['date'] = $item->date;
            $temp['type'] = $item->type;
            $temp['total_price'] = $item->total_price;
            $temp['discount'] = $item->discount;
            $temp['prepayment']['price'] = $item->property()->exists()? $item->property->prepayment_price:null;
            $temp['prepayment']['debt'] = $item->property()->exists()? $item->property->prepayment_debt:null;
            $temp['prepayment']['count'] = $item->property()->exists()? $item->property->prepayment_count:null;
            $temp['prepayment']['num'] = $item->property()->exists()? $item->property->prepayment_num:null;
            $temp['installments'] = $item->installments;
            $temp['installments_num'] = $item->installments_num;
            $temp['products'] = $item->products;
            return $temp;
        });
        if($request->ajax()){
            $view=view('Admin.factor.editAjax', compact('factor', 'user', 'type', 'comments', 'items' , 'changes'))->render();
            return response([
                'data'=>[
                    'view'=> $view
                ]
            ]);
        }
        return view('Admin.factor.edit', compact('factor', 'user', 'type', 'comments', 'items'));
    }
    /**
     * update factor
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $factor=Factor::where('resnumber',$request->resnumber)->first();
        $inputs = $request->all();
        $inputs['price'] = isset($request->price) ? (float) str_replace(',', '', $inputs['price']) : $factor->price;
        unset($inputs['support_id']);
        unset($inputs['bill_image']);

        if (!is_null($request->support_id)) {
            $factor->update([
                'callcenter_id' => $request->support_id,
                'support_id' => $request->support_id,
            ]);
            $factor->task ? $factor->task->update(['user_id' => $factor->callcenter_id]) : '';
        }

        if ($request->status == 1 && $factor->status != 1 )
        {
            $result = self::changeStatusToPaid($request , $factor , $inputs);
            $message = $result['message'];
            $status = $result['status'];
        }
        elseif($request->status != 1 && $factor->status == 1 )
        {
            Factor::returnPaymentBydeleteFactor($factor);
            $message = 'با موفقیت ثبت شد';
            $status = 'success';
        }
        else
        {
            $factor->update($inputs);

            if (!is_null($factor->task_id) && $factor->status == 0) {
                $factor->task->update([
                    'status' => 2,
                ]);
            }
            $message = 'با موفقیت ثبت شد';
            $status = 'success';
        }

        return \response([
            'status'=> $status,
            'data'=>[
                'bill_image_link'=>url($factor->bill_image)
            ],
            'message'=> $message
        ]);
    }

    /**
     * delete factor
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Foundation\Application|\Illuminate\Http\Response
     */
    public function destroy(Factor $factor , Request $request)
    {
        if(auth()->user()->hasPermission('factor-delete'))
        {
            $factor->delete();
            $factor->factorItems()->delete();
            Factor::returnPaymentBydeleteFactor($factor);
            if ($request->ajax()) {
                return response([
                    'status' => 'success',
                    'message'=>'عملیات موفق'
                ]);
            }
            return redirect()->back()->with( [
                'delete' => 'success'
            ] );
        }
        if ($request->ajax()) {
            return response([
                'status' => 'unauthorized',
                'message' => 'شما مجاز به انجام این کار نیستید.'
            ]);
        }
        return redirect()->back()->with( [
            'failByMessage' => 'شما مجاز به انجام این کار نیستید.'
        ] );
    }

    public function factor_manager(Request $request)
    {
        $this->validate($request , [
            'type' => 'required',
        ]);

        if ($request->type == 'delete'){
            if(auth()->user()->hasPermission('factor-delete')) {
                Factor::whereIn('id', $request->id)->delete();
            }
        }

        return response([
            'operator' => true,
        ]);
    }

    public function offer(Request $request)
    {
        if ($request->ajax()) {
            $type = $request->input('type');
            $id = $request->input('id');
            if ($type == 1) {
                Factor::where('id', $id)->update(array('confirm' => '0'));
            }
        }
    }

    public function onner(Request $request)
    {
        if ($request->ajax()) {
            $type = $request->input('type');
            $id = $request->input('id');
            if ($type == 1) {
                Factor::where('id', $id)->update(array('confirm' => '1'));
            }
        }
    }

    public function sms(Request $request)
    {
        if ($request->ajax()) {
            $factor = Factor::find($request->id);

            event(new smsService($factor->user, $factor->service, $factor));

            return response(['operator' => true]);
        }
    }

    public function comment(Request $request)
    {
        $this->validate($request, [
            'comment' => 'required|string',
            'factor_id' => 'required|numeric'
        ]);
        $factor = Factor::findOrFail($request->input('factor_id'));

        $comment= $factor->comments()->create([
            'user_id' => auth()->user()->id,
            'comment' => nl2br($request->input('comment')),
        ]);
        $comment->jalali_created_at=verta($comment->created_at)->format('%d %B ، %Y - H:i:s');
        if ($request->ajax()){
            return \response([
                'status'=>'success',
                'data'=>[
                    'comment'=>$comment,
                    'user'=>$comment->user
                ]
            ]);
        }
        return redirect()->back()->with([
            'save' => true
        ]);
    }

    /**
     * calculate income of system
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Foundation\Application|\Illuminate\View\View
     */
    public function systemIncome()
    {
        if (!auth()->user()->hasPermission('system-income')) {
            abort(403);
        }
        $factors= Factor::where('status',1)->whereIn('type' , Factor::getCashPaymentType())->latest()->get()->each(function ($item){
            $jalali_created_at= verta($item->created_at)->format('%B / %Y ');;
            $exploded_date=explode('/',$jalali_created_at);
            $item->jalali_year=$exploded_date[1];
            $item->jalali_month=$exploded_date[0];
        })->groupBy('jalali_year')->map(function (Collection $item) {
            return $item->groupBy('jalali_month');
        });
        return view('Admin.factor.systemIncome',compact('factors'));
    }

    private function savePaymentRepresentation($inputs , $factor)
    {
        if(auth()->user()->wallet - $inputs['price'] >= 0)
        {
            $factor->update($inputs);
            auth()->user()->update([
                'wallet' => auth()->user()->wallet - $factor->price
            ]);

            Factor::savePayment($factor);
            $message = 'با موفقیت ثبت شد.';
            $status = 'success';
        }
        else
        {
            $message = 'موجودی کیف پول شما برای ثبت پرداختی این فاکتور کافی نمی باشد.';
            $status = 'error';
        }

        return ['message' => $message , 'status' => $status];
    }

    private function changeStatusToPaid($request , $factor , $inputs)
    {
        if(auth()->user()->representation_id)
        {
            $result = self::savePaymentRepresentation($inputs , $factor);
            $message = $result['message'];
            $status = $result['status'];
        }
        else
        {
            $factor->update($inputs);
            Factor::savePayment($factor);
            //upload bill_image cash factor
            $imgbill = $request->file('bill_image');
            if ($imgbill) {
                $url_bill_image = Helper::uploadImage($imgbill , 'document');
                $factor->update(['bill_image' => $url_bill_image]);
            }
            $message = 'با موفقیت ثبت شد.';
            $status = 'success';
        }

        return ['message' => $message , 'status' => $status];
    }
    /**
     *@return \Illuminate\Contracts\View\View|\Illuminate\Contracts\View\Factory
     */
    public function deletedList()
    {
        $user = auth()->user();
        if ($user->isAbleTo('factor-deleted-list')){
            $title = "deleted_list";
            return view('Admin.factor.delete' , compact('title'));
        }
        return abort(401);
    }
    /**
     *@return \Illuminate\Http\Response|\Symfony\Component\HttpFoundation\BinaryFileResponse|\App\Http\Resources\FactorCollection
     */
    public function filterDeletedList()
    {
        $factors = Factor::onlyTrashed()->filter()->latest();
        return new FactorCollection($factors,$factors->paginate(20));
    }
}
