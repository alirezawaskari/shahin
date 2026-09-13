<?php

namespace App\Http\Controllers\Admin;

use App\Events\sendlinkFactor;
use App\Events\smsService;
use App\Exports\FactorsExport;
use App\Helpers\Helper;
use App\Helpers\MediaLibraryHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\AuditCollection;
use App\Http\Resources\FactorCollection;
use App\Models\Factor;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Psy\Command\ListCommand\FunctionEnumerator;

class FactorController extends Controller
{
    use \App\Http\Controllers\Concerns\TransfersRecordsToBusiness;

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
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
                    Factor::filter()->where('representation_id', $user->admin_representation->id)->whereStatus(true)->whereIn('type' , Factor::getCashPaymentType())->sum('price'),
                    Factor::filter()->where('representation_id', $user->admin_representation->id)->where('status' , '!=' , true)->whereIn('type' , Factor::getCashPaymentType())->sum('price')
                ];
                $day_values = [
                    Factor::filter()->where('representation_id', $user->admin_representation->id)->whereDate('created_at', Helper::vertaTocarbon(verta()->today()))->whereStatus(true)->whereIn('type' , Factor::getCashPaymentType())->sum('price'),
                    Factor::filter()->where('representation_id', $user->admin_representation->id)->whereDate('created_at', Helper::vertaTocarbon(verta()->today()))->where('status' , '!=' , true)->whereIn('type' , Factor::getCashPaymentType())->sum('price')
                ];

            } else {
                $values = [
                    Factor::filter()->where('representation_id', $user->admin_representation->id)->whereStatus(true)->whereIn('type' , Factor::getCashPaymentType())->sum('price'),
                    Factor::filter()->where('representation_id', $user->admin_representation->id)->where('status' , '!=' , true)->whereIn('type' , Factor::getCashPaymentType())->sum('price')
                ];
                $day_values = [
                    Factor::filter()->where('representation_id', $user->admin_representation->id)->whereDate('created_at', Helper::vertaTocarbon(verta()->today()))->whereStatus(true)->whereIn('type' , Factor::getCashPaymentType())->sum('price'),
                    Factor::filter()->where('representation_id', $user->admin_representation->id)->whereDate('created_at', Helper::vertaTocarbon(verta()->today()))->where('status' , '!=' , true)->whereIn('type' , Factor::getCashPaymentType())->sum('price')
                ];

            }
        } else {
            if ($user->hasPermission('factor-detail')) {

                $values = [
                    Factor::filter()->whereStatus(true)->sum('price'),
                    Factor::filter()->where('status' , '!=' , true)->sum('price')
                ];
                $day_values = [
                    Factor::filter()->whereDate('created_at', Helper::vertaTocarbon(verta()->today()))->whereStatus(true)->whereIn('type' , Factor::getCashPaymentType())->sum('price'),
                    Factor::filter()->whereDate('created_at', Helper::vertaTocarbon(verta()->today()))->where('status' , '!=' , true)->whereIn('type' , Factor::getCashPaymentType())->sum('price')
                ];

            } else {
                $values = [
                    Factor::filter()->whereStatus(true)->sum('price'),
                    Factor::filter()->where('status' , '!=' , true)->sum('price')
                ];
                $day_values = [
                    Factor::filter()->whereDate('created_at', Helper::vertaTocarbon(verta()->today()))->whereStatus(true)->whereIn('type' , Factor::getCashPaymentType())->sum('price'),
                    Factor::filter()->whereDate('created_at', Helper::vertaTocarbon(verta()->today()))->where('status' , '!=' , true)->whereIn('type' , Factor::getCashPaymentType())->sum('price')
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

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
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

    /**
     * @param Request $request
     * @return FactorCollection|\Illuminate\Http\Response|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function filter(Request $request)
    {

        $factors = Factor::filter()->latest();

        if ($request->exel == 'on') {
            return (new FactorsExport($factors))->download('factors.xlsx');
        }
        return new FactorCollection($factors,$factors->paginate(20));
    }

    /**
     * @param Request $request
     * @return FactorCollection|\Illuminate\Http\Response|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function filterDesktop(Request $request)
    {
        $factors = Factor::where('status', 1)->where('confirm' , 0)->filter()->latest();

        if ($request->exel == 'on') {
            return (new FactorsExport($factors))->download('factors.xlsx');
        }
        return new FactorCollection($factors->get(),$factors->paginate(20));
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function create()
    {
        return view('Admin.factor.create');
    }

    /**
     * @param Factor $factor
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application|\Illuminate\Http\Response
     */
    public function edit(Factor $factor, Request $request)
    {
        $user = $factor->user;
        $type = $factor->factorable_type;
        $comments = Helper::userHistories($user);
        $changes = $factor->audits()->with('user')->get();
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
        $user = $factor->user;

        // فیلد «کسب و کار» فرم: تغییرش یعنی انتقال همین فاکتور به کسب و کار دیگر.
        $businessTransfer = $this->applyInlineBusinessTransfer(
            $request,
            'factor',
            [$factor->id],
            $factor->ghorfeOnlineLists->modelKeys()
        );
        // ستون جدول فاکتور نیست؛ انتقال بالاتر انجام شده است.
        unset($inputs['destination_business_id']);

        $inputs['price'] = isset($request->price) ? (float) str_replace(',', '', $inputs['price']) : $factor->price;
        unset($inputs['support_id']);
        unset($inputs['bill_image']);
        // `url_bill_image` carries the ajax-uploaded temp media id, not a column - keep it out
        // of the mass-assigned update() and attach it explicitly after the status branches.
        unset($inputs['url_bill_image']);

        // The payment receipt is required for card-to-card, POS (card reader) and cash payments.
        // It is optional for every other type, and never required when the factor already carries
        // a receipt (edit that isn't re-uploading). Enforced here server-side because the ajax
        // upload widget's file input is display:none and cannot use HTML5 `required` (that aborts
        // submit as "not focusable"); a client-side check gives immediate feedback but this is the gate.
        if (in_array($request->type, ['card', 'cash', 'card_reader'], true)
            && !$request->url_bill_image
            && !$factor->bill_image) {
            $receiptError = 'برای پرداخت کارت به کارت، کارتخوان و نقدی، بارگذاری رسید دریافت وجه الزامی است.';
            if ($request->ajax()) {
                return \response(['status' => 'error', 'message' => $receiptError], 422);
            }
            return redirect()->back()->withInput()->with(['failByMessage' => $receiptError]);
        }

        if ( $request->input( 'payment_type' ) == 'credit' ) {
            $result = User::checkCredit( $user, $inputs['price'], $request->input( 'password' ) , $request->input('title'));
            if ( !$result[ 'success' ] ) {
                return redirect()->back()->with( [
                    'failByMessage' => $result[ 'message' ]
                ] );
            }
        }
        if ( $request->input( 'payment_type' ) == 'wallet' ) {
            $result = User::checkWallet( $user, $inputs['price'], $request->input( 'password' ) );
            if ( !$result[ 'success' ] ) {
                return redirect()->back()->with( [
                    'failByMessage' => $result[ 'message' ]
                ] );
            }
        }
        unset($inputs['password']);
        unset($inputs['phone']);
        if (!is_null($request->support_id)) {
            $callcenter = User::find($request->support_id);
            $factor->update([
                'callcenter_id' => $request->support_id,
                'support_id' => $request->support_id,
                'representation_id' => $callcenter->admin_representation_id,
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
            $factor->update([
                'status' => $request->status,
            ]);
            $message = 'با موفقیت ثبت شد';
            $status = 'success';
        }
        else
        {
            $factor->update($inputs);

            if ($factor->task && $factor->status == 0) {
                $factor->task->update([
                    'status' => 2,
                ]);
            }
            $message = 'با موفقیت ثبت شد';
            $status = 'success';
        }

        // Ajax pipeline: the receipt was pre-uploaded to a temp holder and its media id
        // arrives in `url_bill_image`. Attach it into the factor's single-file collection on
        // ANY save that carries one - not only the unpaid->paid transition - otherwise
        // re-uploading a receipt on an already-paid factor is silently dropped. The legacy
        // `bill_image` column is accessor-shadowed (Factor::getBillImageAttribute) and not written.
        if ($request->url_bill_image) {
            MediaLibraryHelper::attachTo($factor, $request->url_bill_image, 'bill_image');
            $factor->refresh();
        }

        // The ajax edit modal (/factor-update) reads this JSON; the standalone full-page
        // edit form (resource route factor.update) is a plain PATCH and must be redirected
        // back with a flash toast instead of being shown the raw JSON.
        if ($request->ajax()) {
            return \response([
                'status'=> $status,
                'data'=>[
                    'bill_image_link'=>url($factor->bill_image)
                ],
                'message'=> $message
            ]);
        }
        return redirect()->back()->with(array_merge(
            $status === 'success' ? ['successByMessage' => $message] : ['failByMessage' => $message],
            $this->businessTransferFlash($businessTransfer)
        ));
    }

    /**
     * @param Factor $factor
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response
     */
    public function destroy(Factor $factor , Request $request)
    {
        if(auth()->user()->hasPermission('factor-delete'))
        {
            $factor->factorItems()->delete();
            Factor::returnPaymentBydeleteFactor($factor);
            $factor->delete();
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

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Foundation\Application|\Illuminate\Http\Response
     * @throws \Illuminate\Validation\ValidationException
     */
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

    /**
     * @param Request $request
     */
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

    /**
     * @param Request $request
     */
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

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Http\Response
     * @throws \Illuminate\Validation\ValidationException
     */
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

    /**
     * @param $inputs
     * @param $factor
     * @return string[]
     */
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

    /**
     * @param $request
     * @param $factor
     * @param $inputs
     * @return string[]
     */
    private function changeStatusToPaid($request , $factor , $inputs)
    {
        // if(auth()->user()->representation_id)
        // {
        //     $result = self::savePaymentRepresentation($inputs , $factor);
        //     $message = $result['message'];
        //     $status = $result['status'];
        // }
        // else
        // {
            $factor->update($inputs);
            Factor::savePayment($factor);
            $message = 'با موفقیت ثبت شد.';
            $status = 'success';
       // }

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
    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function restore(Request $request)
    {
        if(auth()->user()->hasPermission('factor-delete'))
        {
            $factor = Factor::onlyTrashed()->find($request->id);
            $factor->restore();
            $factor->factorItems()->withTrashed()->restore();
            Factor::savePayment($factor);
            if ($request->ajax()) {
                return response([
                    'status' => 'success',
                    'message'=>'عملیات موفق'
                ]);
            }
        }
    }
    /**
     * @param \App\Models\Factor $factor
     * @return \Illuminate\Http\Response|\Illuminate\Contracts\Routing\ResponseFactory
     */
    public function sendMe(Factor $factor)
    {
        event(new sendlinkFactor(auth()->user(), $factor, auth()->user()->family ,'online'));
        return response([
            'status' => 'success',
            'message'=>'لینک باموفقیت برای شما ارسال شد.'
        ]);
    }
}

