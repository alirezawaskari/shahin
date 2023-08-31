<?php

namespace App\Http\Controllers;

use App\Helpers\Helper;
use App\Models\ContractForm;
use App\Models\Coupon;
use App\Models\Factor;
use App\Models\FactorItem;
use App\Models\ServiceItem;
use App\Models\TotalFactorItem;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class FactorItemController extends Controller
{
    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function create(Request $request)
    {
        $cart = session()->get('cart');
        $factor = auth()->user()->factors()->create([
            'status' => false,
            'confirm' => false,
            'price' => $cart['total']['price'] + $cart['total']['price'] * 9 / 100,
            'tax' => $cart['total']['price'] * 9 / 100,
            'count' => $cart['total']['count'],
            'representation_id' => $cart['representation'] == 0 ? null : $cart['representation'],
            'expired_at' => now()->addDay(3),
        ]);
        foreach ($cart['items'] as $item) {
            $factor->factor_items()->create([
                'factoritemable_id' => $item['type'] == 'service' ? $item['id'] : ServiceItem::find($item['id'])->service->id,
                'service_item_id' => $item['type'] == 'service' ? null : $item['id'],
                'count' => 1,
                'price' => $item['price'],
            ]);
        }
        session()->forget('cart');
        return redirect(route('factor', [$factor->resnumber]));
    }

    /**
     * @param FactorItem $factorItem
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function index(FactorItem $factorItem)
    {
        if (!$factorItem->status) {
           $factorItem->load('factor');
            return view('site.factorItem', compact('factorItem'));
        }
        return redirect(route('profile.factor'));
    }

    /**
     * @param FactorItem $factorItem
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function show(FactorItem $factorItem)
    {
        $expire_installments = $factorItem->factorItemInstallments->where('status', 0)->where('date', '<', Carbon::now());
        $paid_installments = $factorItem->factorItemInstallments->where('status', 1);
        $factor = $factorItem->factor;
        $user = $factorItem->factor->user;
        $type = $factorItem->factor->factorable_type;
        $comments = $factorItem->factor->comments;
        $item = [];
        $item['id'] = $factorItem->id;
        $item['service'] = $factorItem->factoritemable;
        $item['service_item'] = !is_null($factorItem->service_item_id) ? $factorItem->serviceItem : null;
        $item['centers'] = $factorItem->factoritemable->centers == true ? $factorItem->factoritemable->centers : null;
        $item['forms'] = count($factorItem->factoritemable->beforeforms) != 0 ? $factorItem->factoritemable->beforeforms : null;
        $item['complete'] = $factorItem->complete;
        $item['values'] = $factorItem->values;
        $item['factor_values'] = $factorItem->factor_values;
        $item['supervisor_id'] = $factorItem->supervisor_id;
        $item['contract_values'] = $factorItem->contract_values;
        $item['factor_image'] = $factorItem->factor_image;
        $item['status_factor'] = $factorItem->status_factor;
		$item['status_mali'] = $factorItem->totalFactorItem->status_mali??$factorItem->status_mali;
	  //$item['status_mali'] = $factorItem->status_mali;
      //$item['status_mali_text'] = FactorItem::statusMali($factorItem->status_mali);
		$item['status_mali_text'] = isset($factorItem->totalFactorItem)?FactorItem::statusMali($factorItem->totalFactorItem->status_mali): FactorItem::statusMali($factorItem->status_mali);


        $item['status_user'] = FactorItem::statusFactor($factorItem->status_factor);
        $item['confirms'] = $factorItem->confirms;
        $item['center'] = $factorItem->center;
        $item['date'] = $factorItem->date;
        $item['type'] = $factorItem->type;
        $item['guarantee_type'] = $factorItem->guarantee_type;
        $item['file_number'] = $factorItem->file_number;
        $item['approval_date'] = verta($factorItem->approval_date)->format('Y/m/d');
        $item['total_price'] = $factorItem->total_price + $factorItem->discount;
        $item['credit'] = $factorItem->credit;
        $item['discount'] = $factorItem->discount;
        $item['extra_pay'] = $factorItem->type ? TotalFactorItem::getExtraPay($factorItem->supervisor->totalFactorItem) : 0;
        $item['prepayment']['price'] = $factorItem->property ? $factorItem->property->prepayment_price : 0;
        $item['price_difference'] = $factorItem->property ? $factorItem->property->price_difference : 0;
        $item['installments_price'] = $factorItem->installments_price;
        $item['installments_num'] = $factorItem->installments_num;
        $item['service_tariffs' ] = $factorItem->serviceTariffs;
        $item['payments'] = $factorItem->factors;
        $item[''] = $factorItem->factors;
        $item['complete'] = $factorItem->status_factor == 6 || $factorItem->status_factor == 10 ? true : false;
        $item['factorItems'] = FactorItem::getFatorItemCollection($factorItem)->get();
        $admin_users = User::where('center_id', $factorItem->center_id)->where('center_id', '!=' , null)->get();
        $doctors = [];
        foreach ($admin_users as $u) {
            if ($u->hasRole('doctor')) {
                $doctors[] = $u;
            }
        }
        $sessions = $factorItem->sessions;
        return view('site.profile.layouts.factoritem.show', compact('expire_installments', 'paid_installments' ,'sessions', 'doctors', 'factor', 'factorItem', 'user', 'type', 'comments', 'item'));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function coupon(Request $request)
    {
        $this->validate($request, [
            'code' => 'required|exists:coupons,code',
            'factor_id' => 'required',
        ]);
        $factor = Factor::findOrFail($request->input('factor_id'));
        $date = Carbon::now()->toDateString();
        $coupon = Coupon::whereStatus(true)->where('start_at', '<=', $date)->where('end_at', '>=', $date)->first();

        if ($coupon) {
            if ($coupon->capacity > $coupon->reserved) {
                $factor->update(['coupon_id' => $coupon->id]);
                $coupon->reserved++;
                $coupon->save();
            } else {
                return redirect()->back()->withErrors(['msg' => 'مهلت کد تخفیف به پایان رسیده است!']);
            }
        } else {
            return redirect()->back()->withErrors(['msg' => 'کد تخفیف مورد نظر یافت نشد!']);
        }
        return redirect()->back();
    }

    /**
     * @param $factor
     * @return float|int
     */
    private static function discount($factor)
    {
        $amount = $factor->price;
        if (!is_null($factor->coupon_id)) {
            $amount = $amount - ($amount * $factor->coupon->discount / 100);
        }
        return $amount;
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function requestContractSettlement(Request $request)
    {
        $total_factor_item = TotalFactorItem::find($request->item['id']);
        if(!in_array($total_factor_item->status_mali , [7,6,8,9]))
        {
            $applicant_id = auth()->user()->id;
            $total_factor_item_contract_settlement = $total_factor_item->totalFactorItemContractSettlement()->create([
                'applicant_id' => $applicant_id,
                'description_request_contract_settlement' => $request->description_request_contract_settlement,
            ]);
            if ($request->urlrequest_contract_settlement) {
                Helper::deleteImage($total_factor_item_contract_settlement->img_request_contract_settlement);
                $total_factor_item_contract_settlement->update(['img_request_contract_settlement' => Helper::moveImage($request->urlrequest_contract_settlement,'document')]);
            }
            $total_factor_item->update(['status_mali' => 7]);
            return redirect()->back()->with([
                'successByMessage' => 'درخواست تسویه حساب با موفقیت ثبت شد.'
            ]);
        }
        return redirect()->back()->with([
            'failByMessage' => 'درخواست تسویه حساب قبلا ثبت شده است.'
        ]);
    }

    /**
     * @param $item
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Foundation\Application|\Illuminate\Http\Response
     */
    public function formData($item)
    {
        $factor_item = FactorItem::find($item['id']);
        $values = $factor_item->factor_values ? $factor_item->factor_values : [];
        $contract_values = $factor_item->contract_values ? $factor_item->contract_values : [];
        foreach ($factor_item->factoritemable->beforeforms as $key => $form) {
            if ($form->data_save_level == 'user') {
                if (isset($item[$form->name])) {
                    User::where('id', $item['user_id'])->update([$form->name => $item[$form->name]]);
                }
                if ($form->type == 'file') {
                    if (isset($item[$form->name])) {
                        $image = $item[$form->name];
                        if ($image) {
                            $urlimage = Helper::uploadImage($image , 'document');
                            User::where('id', $item['user_id'])->update([$form->name => $urlimage]);
                        }
                    }
                }
            }
            if ($form->data_save_level == 'factor_item_factor_values') {
                if ($form->type == 'file') {
                    if (isset($item[$form->name])) {
                        $image = $item[$form->name];
                        if ($image) {
                            $urlimage = Helper::uploadImage($image , 'document');
                            $values[$form->name] = $urlimage;
                            $values = Arr::add($values, $form->name, $item[$form->name]);
                        }
                    }
                }
                elseif ($form->type == 'multi_file') {
                    if (isset($item[$form->name])) {
                        $images = isset($values[$form->name]) ? $values[$form->name] : [];
                        $files = $item[$form->name];
                        foreach ($files as $file) {
                            if ($file) {
                                $url = Helper::uploadImage($file , 'document');
                                if (isset($images)) {
                                    $images[] = $url;
                                    $values[$form->name] = $images;
                                }
                            }
                        }
                        $values = Arr::add($values, $form->name, $images);
                    }
                }
                else{
                    if (isset($item[$form->name])) {
                        if (isset($values[$form->name])) {
                            $values[$form->name] = $item[$form->name];
                        }
                        $values = Arr::add($values, $form->name, $item[$form->name]);
                    }
                }
            }
            $factor_item->update([
                'factor_values' => !is_null($values) ? $values : null,
            ]);
        }
        foreach (ContractForm::all() as $key => $contract_form) {
            if ($contract_form->type == 'other_document_image') {
                if (isset($item[$contract_form->name])) {
                    $images = isset($contract_values[$contract_form->name]) ? $contract_values[$contract_form->name] : [];
                    $files = $item[$contract_form->name];
                    foreach ($files as $file) {
                        if ($file) {
                            $url = Helper::uploadImage($file , 'document');
                            if (isset($images)) {
                                $images[] = $url;
                                $contract_values[$contract_form->name] = $images;
                            }
                        }
                    }
                    $contract_values = Arr::add($contract_values, $contract_form->name, $images);
                }
            }
            if ($contract_form->type == 'file') {
                if (isset($item[$contract_form->name])) {
                    $img = $item[$contract_form->name];
                    if ($img) {
                        $url = $item['url'.$contract_form->name];
                        if (isset($contract_values[$contract_form->name])) {
                        $contract_values[$contract_form->name] != $url ? Helper::deleteImage($contract_values[$contract_form->name]) : '';
                            $contract_values[$contract_form->name] = $url;
                        }
                        $contract_values = Arr::add($contract_values, $contract_form->name, $url);
                    }
                }
            }
            if ($contract_form->type == 'text' || $contract_form->type == 'select') {
                if (isset($item[$contract_form->name])) {
                    if (isset($contract_values[$contract_form->name])) {
                        $contract_values[$contract_form->name] = $item[$contract_form->name];
                    }
                    $contract_values = Arr::add($contract_values, $contract_form->name, $item[$contract_form->name]);
                }
            }
            $factor_item->update([
                'contract_values' => !is_null($contract_values) ? $contract_values : null,
            ]);
        }
        return response('true');
    }

    /**
     * @param Request $request
     * @return string
     */
    public function uploadSingleImage(Request $request)
    {
        return Helper::uploadImage($request->file , 'document');
    }
}
