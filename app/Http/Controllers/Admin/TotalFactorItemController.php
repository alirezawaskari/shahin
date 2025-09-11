<?php

namespace App\Http\Controllers\Admin;

use App\Classes\External\SendSms;
use App\Events\factorItemActivation;
use App\Events\factorItemClosing;
use App\Events\sendlinkFactor;
use App\Events\smsNotAnswer;
use App\Exports\FactorItemsExport;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Resources\TotalFactorItemCollection;
use App\Http\Resources\TotalFactorItemInstallmentCollection;
use App\Models\ContractForm;
use App\Models\ContractView;
use App\Models\Factor;
use App\Models\FactorItem;
use App\Models\FinancialPlansType;
use App\Models\Impunity;
use App\Models\Province;
use App\Models\Session;
use App\Models\TotalFactorItem;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\View;
use Illuminate\Validation\Rule;

class TotalFactorItemController extends Controller
{
    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function index()
    {
        $title = 'فهرست پرونده های اقساطی';
        $url = 'filter';
        return view('Admin.totalfactoritem.all', compact('title', 'url'));
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function desktop()
    {
        $title = 'میزکار پرونده های اقساطی';
        $url = 'desktop/filter';
        return view('Admin.totalfactoritem.all', compact('title', 'url'));
    }

    //show detail installments file

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function reports()
    {
        $title = 'گزارش مالی پرونده ها';
        $url = 'reports/filter';
        return view('Admin.totalfactoritem.report', compact('title', 'url'));
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function desktopReports()
    {
        $title = 'میزکار گزارش مالی پرونده ها';
        $url = 'reports/desktop/filter';
        return view('Admin.totalfactoritem.report', compact('title', 'url'));
    }

    /**
     * @param TotalFactorItem $totalFactorItem
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function show(TotalFactorItem $totalFactorItem)
    {
        //show detail file
        $installments = $this->fetchInstallments($totalFactorItem);
        $factor = $totalFactorItem->factor;
        $user = $totalFactorItem->factor->user;
        $comments = Helper::userHistories($user);
        $expire_installments = $totalFactorItem->totalFactorItemInstallments->where('status', 0)->where('date', '<', Carbon::now());
        $paid_installments = $totalFactorItem->totalFactorItemInstallments->where('status', 1);
        //set totalFactorItem items
        $item = self::setItem($totalFactorItem);
        //set totalFactorItem factorItems items
        $items = self::setFactorItemItems($totalFactorItem);
        $provinces = Province::all();
        $histories = $totalFactorItem->histories;
        $user_histories = Helper::userHistories($totalFactorItem->factor->user);

        $resnumber = TotalFactorItem::setFatorItemCollectionResnumber($totalFactorItem);
        $contract_number = TotalFactorItem::setAgentContractNumber($totalFactorItem);

        $checks = $totalFactorItem->checks;
        $promissories = $totalFactorItem->promissories;
        return view('Admin.totalfactoritem.show', compact('resnumber', 'contract_number', 'histories', 'user_histories', 'provinces', 'expire_installments', 'paid_installments', 'factor', 'totalFactorItem', 'user', 'comments', 'item', 'items', 'installments', 'checks', 'promissories'));
    }
    /**
     * @param \App\Models\TotalFactorItem $totalFactorItem
     * @return array
     */
    private function setItem(TotalFactorItem $totalFactorItem)
    {
        $item['id'] = $totalFactorItem->id;
        $item['contract_forms'] = ContractForm::orderBy('position', 'Asc')->get();
        $item['contract_values'] = $totalFactorItem->contract_values;
        $item['guarantee_type'] = $totalFactorItem->guarantee_type;
        $item['file_number_mali'] = $totalFactorItem->file_number_mali ? $totalFactorItem->file_number_mali : $totalFactorItem->id;
        $item['approval_date'] = $totalFactorItem->approval_date ? $totalFactorItem->approval_date : now();
        $item['credit'] = TotalFactorItem::calculateCredit($totalFactorItem);
        $item['total_debt'] = TotalFactorItem::calculateTotalDebt($totalFactorItem);
        $item['discount'] = $totalFactorItem->factorItems->sum('discount');
        $item['prepayment']['price'] = $totalFactorItem->property()->exists() ? $totalFactorItem->property->prepayment_price : 0;
        $item['prepayment']['debt'] = $totalFactorItem->property()->exists() ? $totalFactorItem->property->prepayment_debt : 0;
        $item['prepayment']['count'] = $totalFactorItem->property()->exists() ? $totalFactorItem->property->prepayment_count : 0;
        $item['prepayment']['num'] = $totalFactorItem->property()->exists() ? $totalFactorItem->property->prepayment_num : 0;
        $item['percent_fines'] = $totalFactorItem->percent_fines;
        $item['status_text'] = TotalFactorItem::statusFactor($totalFactorItem->total_factor_item_status_id);
        $item['status'] = $totalFactorItem->total_factor_item_status_id;
        $item['status_mali'] = TotalFactorItem::statusMali($totalFactorItem->status_mali);
        $item['factoritemable'] = TotalFactorItem::setFactorItemable($totalFactorItem);
        $item['price_difference']['installment'] = isset($totalFactorItem->property->price_difference_installment) ? $totalFactorItem->property->price_difference_installment : 0;
        $item['price_difference']['total'] = isset($totalFactorItem->property->price_difference_total) ? $totalFactorItem->property->price_difference_total : 0;
        $item['price_difference']['debt'] = isset($totalFactorItem->property->price_difference_debt) ? $totalFactorItem->property->price_difference_debt : 0;
        $item['installments_price'] = $totalFactorItem->totalFactorItemInstallments->first() ? $totalFactorItem->totalFactorItemInstallments->first()->price : 0;
        $item['installments_num'] = $totalFactorItem->installments_num;
        $item['installments_num_pay'] = $totalFactorItem->totalFactorItemInstallments->where('status', 1)->count();
        $item['extra_pay'] = TotalFactorItem::getExtraPay($totalFactorItem);
        $item['extra_pay_debt'] = number_format(TotalFactorItem::getDebtExtraPay($totalFactorItem));
        $item['loan'] = $totalFactorItem->totalFactorItemInstallments->sum('price');
        $item['total_price'] = $totalFactorItem->total_price + $totalFactorItem->factorItems->sum('discount');
        $item['wage'] = ($item['total_price'] - $item['prepayment']['price']) * 0.04 / 12 * $item['installments_num'];
        $item['guarantee_price'] = self::getGuaranteePrice($item);
        $item['installments_debt'] = TotalFactorItem::getDebtExtraPay($totalFactorItem) + $item['loan'] - $item['installments_num_pay'] * $item['installments_price'];
        $item['credit_amount'] = isset($totalFactorItem->property->price_difference_installment) ? $totalFactorItem->property->price_difference_installment : 0;
        $item['start_date'] = verta($totalFactorItem->approval_date)->format('Y/m/d');
        $item['end_date'] = isset($totalFactorItem->totalFactorItemInstallments->last()->date) ? verta($totalFactorItem->totalFactorItemInstallments->sortBy('date')->last()->date)->format('Y/m/d') : '';
        $item['payments'] = $totalFactorItem->factors;
        $item['payment'] = $totalFactorItem->factor;
        $item['confirm'] = $totalFactorItem->confirm;
        $item['has_payment'] = $totalFactorItem->factors()->where('status', 1)->exists();
        $item['date_first_pay'] = isset($totalFactorItem->factors->where('title', '!=', 'register')->where('status', true)->first()->created_at) ? verta($totalFactorItem->factors->where('title', '!=', 'register')->first()->created_at)->format('Y/m/d') : '';
        $item['guarantee_types_list'] = TotalFactorItem::getGuaranteeTypeByFinancialPlansType($totalFactorItem);
        return $item;
    }

    /**
     * @param \App\Models\TotalFactorItem $totalFactorItem
     * @return array
     */
    private function setFactorItemItems(TotalFactorItem $totalFactorItem)
    {
        $items = $totalFactorItem->factorItems->map(function ($factor_item) {
            $temp = [];
            $temp['id'] = $factor_item->id;
            $temp['resnumber'] = $factor_item->resnumber;
            $temp['file_number'] = $factor_item->file_number;
            $temp['factoritemable'] = $factor_item->factoritemable;
            $temp['service_item'] = !is_null($factor_item->service_item_id) ? $factor_item->serviceItem : null;
            $temp['centers'] = FactorItem::getSelectableCenters($factor_item);
            $temp['forms'] = isset($factor_item->factoritemable->beforeforms) ? $factor_item->factoritemable->beforeforms : null;
            $temp['site_forms'] = isset($factor_item->factoritemable->beforeforms) ? $factor_item->factoritemable->beforeforms->where('level', 'site') : null;
            $temp['complete'] = $factor_item->complete;
            $temp['values'] = $factor_item->values;
            $temp['factor_values'] = $factor_item->factor_values;
            $temp['supervisor_id'] = $factor_item->supervisor_id;
            $temp['factor_image'] = $factor_item->factor_image;
            $temp['confirms'] = $factor_item->confirms;
            $temp['center'] = $factor_item->center;
            $temp['date'] = $factor_item->date ? $factor_item->date : now();
            $temp['type'] = $factor_item->type;
            $temp['discount'] = $factor_item->discount;
            $temp['installments_num'] = $factor_item->installments_num;
            $temp['installments_price'] = $factor_item->installments_price;
            $temp['prepayment']['price'] = isset($factor_item->property->prepayment_price) ? $factor_item->property->prepayment_price : 0;
            $temp['prepayment']['debt'] = isset($factor_item->property->prepayment_debt) ? $factor_item->property->prepayment_debt : 0;
            $temp['prepayment']['num'] = isset($factor_item->property->prepayment_num) ? $factor_item->property->prepayment_num : 0;
            $temp['prepayment']['count'] = isset($factor_item->property->prepayment_count) ? $factor_item->property->prepayment_count : 0;
            $temp['price_difference']['installment'] = isset($factor_item->property->price_difference_installment) ? $factor_item->property->price_difference_installment : 0;
            $temp['price_difference']['total'] = isset($factor_item->property->price_difference_total) ? $factor_item->property->price_difference_total : 0;
            $temp['price_difference']['debt'] = isset($factor_item->property->price_difference_debt) ? $factor_item->property->price_difference_debt : 0;
            $temp['total_price'] = $factor_item->total_price + $factor_item->discount;
            $temp['status'] = FactorItem::statusMali($factor_item->status_mali);
            $temp['products'] = $factor_item->products;
            $temp['status'] = FactorItem::statusMali($factor_item->status_mali);
            $temp['status_factor'] = $factor_item->status_factor;
            $temp['products'] = $factor_item->products;
            $temp['service_tariffs'] = $factor_item->serviceTariffs;
            $temp['date_first_pay'] = isset($factor_item->factors->where('title', '!=', 'register')->where('status', true)->first()->created_at) ? verta($factor_item->factors->where('title', '!=', 'register')->first()->created_at)->format('Y/m/d') : '';
            $temp['confirm'] = $factor_item->totalFactorItem->confirm;
            $temp['factorItems'] = FactorItem::getFatorItemCollection($factor_item)->get();
            $temp['confirm'] = $factor_item->totalFactorItem->confirm;
            $temp['sessions'] = $factor_item->sessions->where('status', '!=', false);
            $temp['financial_plans_type'] = FactorItem::getFnancialPlansType($factor_item);
            $temp['financial_plans_types_list'] = FinancialPlansType::getFinancialPlansTypeByItem($factor_item->factoritemable);

            return $temp;
        });
        //set factorItems centers
        foreach ($items as $supervisor_factor_item) {
            foreach ($supervisor_factor_item['factorItems'] as $factor_item) {
                $factor_item['centers'] = FactorItem::getSelectableCenters($factor_item);
            }
        }
        return $items;
    }

    /**
     * @param TotalFactorItem $totalFactorItem
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application|\Illuminate\Http\RedirectResponse
     */
    public function installments(TotalFactorItem $totalFactorItem)
    {
        //checking the permission of show installments
        if (auth()->user()->isAbleTo('file-report')) {
            //checking the existence of totalFactorItem installments
            if (!count($totalFactorItem->totalFactorItemInstallments)) {
                return redirect()->back()->with([
                    'failByMessage' => 'فهرست اقساطی برای این پرونده تشکیل نشده است.'
                ]);
            }
        } else {
            return redirect()->back()->with([
                'failByMessage' => 'شما مجاز به انجام این کار نیستید'
            ]);
        }
        $user = $totalFactorItem->factor->user;
        $installments = $this->fetchInstallments($totalFactorItem);


        $expire_installments = TotalFactorItem::getExpireInstallments($totalFactorItem);
        $comments = Helper::userHistories($user);

        $item = [];
        $item['impunity_penalty_amount'] = !is_null($totalFactorItem->impunityPenalty) ? $totalFactorItem->impunityPenalty->amount : 0;
        $item['impunity_installment_amount'] = !is_null($totalFactorItem->impunityInstallment) ? $totalFactorItem->impunityInstallment->amount : 0;
        $item['contract_forms'] = ContractForm::orderBy('position', 'Asc');
        $item['contract_values'] = $totalFactorItem->contract_values;
        $item['id'] = $totalFactorItem->id;
        $item['type'] = $totalFactorItem->type;
        $item['prepayment']['price'] = $totalFactorItem->property->prepayment_price;
        $item['prepayment']['debt'] = $totalFactorItem->property->prepayment_debt;
        $item['prepayment']['count'] = $totalFactorItem->property->prepayment_count;
        $item['prepayment']['num'] = $totalFactorItem->property->prepayment_num;
        $item['status'] = TotalFactorItem::statusMali($totalFactorItem->status_mali);
        $item['price'] = $expire_installments->sum('price');
        $item['total_price'] = $totalFactorItem->total_price + $totalFactorItem->discount;
        $item['price_difference']['installment'] = $totalFactorItem->property->price_difference_installment;
        $item['price_difference']['total'] = $totalFactorItem->property->price_difference_total;
        $item['price_difference']['debt'] = $totalFactorItem->property->price_difference_debt;
        $item['installments_num'] = $totalFactorItem->installments_num;
        $item['installments_num_pay'] = $totalFactorItem->totalFactorItemInstallments->where('status', 1)->count();
        $item['installments_price_pay'] = $totalFactorItem->totalFactorItemInstallments->where('status', 1)->sum('price');
        $item['extra_pay'] = TotalFactorItem::getExtraPay($totalFactorItem);
        $item['extra_pay_debt'] = number_format(TotalFactorItem::getDebtExtraPay($totalFactorItem));
        $item['loan'] = $totalFactorItem->totalFactorItemInstallments->sum('price');
        $item['credit_amount'] = isset($totalFactorItem->property->price_difference_installment) ? $totalFactorItem->property->price_difference_installment : 0;
        $item['installments_debt'] = (TotalFactorItem::getDebtExtraPay($totalFactorItem) + $item['loan'] - $item['installments_price_pay']) - ($item['credit_amount'] + $item['impunity_installment_amount']);
        $item['start_date'] = verta($totalFactorItem->approval_date)->format('Y/m/d');
        $item['end_date'] = verta($totalFactorItem->totalFactorItemInstallments->sortBy('date')->last()->date)->format('Y/m/d');
        $item['payments'] = $totalFactorItem->factors;
        $item['payment'] = $totalFactorItem->factor;
        $item['total_debt_file'] = TotalFactorItem::financialBalance($totalFactorItem);
        $item['financial_balance_items'] = TotalFactorItem::financialBalance($totalFactorItem);
        $item['file_number_mali'] = $totalFactorItem->file_number_mali ? $totalFactorItem->file_number_mali : 'ندارد';

        $items = $totalFactorItem->factorItems->map(function ($factor_item) {
            $temp = [];
            $temp['id'] = $factor_item->id;
            $temp['factoritemable'] = $factor_item->factoritemable;

            return $temp;
        });

        return view('Admin.totalfactoritem.installment', compact('comments', 'item', 'items', 'user', 'totalFactorItem', 'expire_installments', 'installments'));
    }

    /**
     * @param Request $request
     * @return TotalFactorItemCollection|\Illuminate\Http\Response|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function filter(Request $request)
    {

        $total_factor_items = TotalFactorItem::filter()->latest();
        if ($request->exel == 'on') {
            return (new FactorItemsExport($total_factor_items))->download('factorItems.xlsx');
        }
        return new TotalFactorItemCollection($total_factor_items->paginate(20));
    }

    /**
     * @param Request $request
     * @return TotalFactorItemCollection|\Illuminate\Http\Response|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function filterDesktop(Request $request)
    {
        if (auth()->user()->hasRole('mali')) {
            $total_factor_items = TotalFactorItem::where('total_factor_item_status_id', 5)->filter();
        }
        if (auth()->user()->hasRole('filing')) {
            $total_factor_items = TotalFactorItem::where('total_factor_item_status_id', 9)->filter()->latest();
        }
        if (auth()->user()->hasRole('superadmin') || auth()->user()->hasRole('superadmin-representation')) {
            $total_factor_items = TotalFactorItem::where('total_factor_item_status_id', 8)->filter();
        }
//        if (auth()->user()->hasRole('admin-site')) {
//            $total_factor_items = TotalFactorItem::whereIn('total_factor_item_status_id',array(0,4,14))->filter()->latest();
//        }
        if (auth()->user()->hasRole('consulting_contract_affairs')) {
            $total_factor_items = TotalFactorItem::where('total_factor_item_status_id', 13)->filter()->latest();
        }
        if ($request->exel == 'on') {
            return (new FactorItemsExport($total_factor_items))->download('factorItems.xlsx');
        }
        return new TotalFactorItemCollection($total_factor_items->paginate(20));
    }

    /**
     * @param Request $request
     * @return TotalFactorItemCollection|\Illuminate\Http\Response|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function filterReport(Request $request)
    {
        $total_factor_items = TotalFactorItem::where('status_mali', '!=', 0)->filter()->latest();
        if (auth()->user()->hasRole('legal')) {
            $total_factor_items = TotalFactorItem::where('status_mali', 10)->filter()->latest();
        }
        if (auth()->user()->hasRole('consulting_contract_affairs')) {
            $total_factor_items = TotalFactorItem::where('status_mali', 4)->where('status_mali', 4)->filter()->latest();
        }
        if ($request->exel == 'on') {
            return (new FactorItemsExport($total_factor_items))->download('factorItems.xlsx');
        }
        return new TotalFactorItemCollection($total_factor_items->paginate(20));
    }

    /**
     * @param Request $request
     * @return TotalFactorItemCollection|\Illuminate\Http\Response|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function filterDesktopReport(Request $request)
    {
        if (auth()->user()->hasRole('mali')) {
            $total_factor_items = TotalFactorItem::where('status_mali', 7)->orWhere('status_mali', 9)->filter();
        }
        if (auth()->user()->hasRole('superadmin') || auth()->user()->hasRole('superadmin-representation')) {
            $total_factor_items = TotalFactorItem::where('status_mali', 8)->filter();
        }
        if (auth()->user()->hasRole('legal')) {
            $total_factor_items = TotalFactorItem::where('status_mali', 10)->filter()->latest();
        }
        if (auth()->user()->hasRole('consulting_contract_affairs')) {
            $total_factor_items = TotalFactorItem::where('status_mali', 4)->filter()->latest();

        }
        if (!auth()->user()->admin_representation) {
            $total_factor_items->whereHas('factor', function ($total_factor_items) {
                $total_factor_items->where('representation_id', auth()->user()->admin_representation_id);
            });
        }
        if ($request->exel == 'on') {
            return (new FactorItemsExport($total_factor_items))->download('factorItems.xlsx');
        }
        return new TotalFactorItemCollection($total_factor_items->paginate(20));
    }

    /**
     * @param TotalFactorItem $totalFactorItem
     * @return \Illuminate\Http\RedirectResponse
     * update status with confirm financial totalFactorItem
     */
    public function update(TotalFactorItem $totalFactorItem)
    {
        $factor_items = $totalFactorItem->factorItems;

        if (auth()->user()->hasPermission('supporter_confirmation') && in_array(trim($totalFactorItem->total_factor_item_status_id), [0, 3, 4, 14])) {
            foreach ($factor_items as $factor_item) {
                $fator_item_collections = FactorItem::getFatorItemCollection($factor_item);

                $fator_item_collections->update([
                    'supporter_id' => auth()->user()->id,
                    'status_factor' => 9,
                ]);
            }
            $totalFactorItem->update(['total_factor_item_status_id' => 9]);

            return redirect()->back()->with([
                'update' => 'success'
            ]);
        }

        if (auth()->user()->hasRole('filing')) {
            $values = $totalFactorItem->factor_values ? $totalFactorItem->factor_values : [];
            $factor_values = Arr::add($values, 'filing', auth()->user()->id);
            $totalFactorItem->update([
                'factor_values' => $factor_values,
                'approval_date' => $totalFactorItem->approval_date ? $totalFactorItem->approval_date : Carbon::now(),
                'total_factor_item_status_id' => 5
            ]);

            foreach ($factor_items as $factor_item) {
                $fator_item_collections = FactorItem::getFatorItemCollection($factor_item);

                $fator_item_collections->update([
                    'approval_date' => $totalFactorItem->approval_date,
                    'status_factor' => 5
                ]);
            }
        }

        if (auth()->user()->hasRole('mali')) {
            $values = $totalFactorItem->factor_values ? $totalFactorItem->factor_values : [];
            $factor_values = Arr::add($values, 'mali', auth()->user()->id);
            $totalFactorItem->update([
                'factor_values' => $factor_values,
                'total_factor_item_status_id' => 13
            ]);
            foreach ($factor_items as $factor_item) {
                $fator_item_collections = FactorItem::getFatorItemCollection($factor_item);

                $fator_item_collections->update([
                    'status_factor' => 13
                ]);
            }
        }

        if (auth()->user()->hasRole('consulting_contract_affairs')) {
            $values = $totalFactorItem->factor_values ? $totalFactorItem->factor_values : [];
            $factor_values = Arr::add($values, 'consulting_contract_affairs', auth()->user()->id);
            $totalFactorItem->update([
                'factor_values' => $factor_values,
                'total_factor_item_status_id' => 18
            ]);
            foreach ($factor_items as $factor_item) {
                $fator_item_collections = FactorItem::getFatorItemCollection($factor_item);
                $user_phone_number = $factor_item->factor->user->phone;
                $approval_date = verta($totalFactorItem->approval_date)->format('Y/m/d');
                $link = 'https://lalecard.ir/factorItem/' . $factor_item->resnumber;
                SendSms::sendSingleSms('sendUserContractConfirmLink', $user_phone_number, ['param1' => $totalFactorItem->id, 'param2' => $approval_date, 'param3' => $link]);
                $fator_item_collections->update([
                    'status_factor' => 18
                ]);
            }

        }

        if (auth()->user()->hasRole('superadmin_representation')) {
            $values = $totalFactorItem->factor_values ? $totalFactorItem->factor_values : [];
            $factor_values = Arr::add($values, 'superadmin_representation', auth()->user()->id);
            $totalFactorItem->update([
                'factor_values' => $factor_values,
                'total_factor_item_status_id' => 8
            ]);
            foreach ($factor_items as $factor_item) {
                $fator_item_collections = FactorItem::getFatorItemCollection($factor_item);

                $fator_item_collections->update([
                    'status_factor' => 8
                ]);
            }

        }

        if (auth()->user()->hasRole('superadmin')) {
            $values = $totalFactorItem->factor_values ? $totalFactorItem->factor_values : [];
            $factor_values = Arr::add($values, 'superadmin', auth()->user()->id);
            $totalFactorItem->update([
                'factor_values' => $factor_values,
                'status_mali' => 1,
                'confirm' => true,
                'approval_date' => $totalFactorItem->approval_date ? $totalFactorItem->approval_date : Carbon::now(),
                'total_factor_item_status_id' => 10,
            ]);

            foreach ($factor_items as $factor_item) {
                $factor_item->update(['credit' => $factor_item->total_price + $factor_item->credit]);

                if ($factor_item->center) {
                    event(new factorItemActivation($factor_item->factor->user, $factor_item->center->title));
                }

                $fator_item_collections = FactorItem::getFatorItemCollection($factor_item);

                $fator_item_collections->update([
                    'status_factor' => 10,
                    'confirm' => true,
                ]);
                //close older sessions
                Session::whereIn('status', [3, 5])->where('factor_item_id', $factor_item->id)->update([
                    'status' => 7,
                    'description' => 'این ویزیت به دلیل اینکه در حالت مشاوره شد نبود و ویزیت جدیدی برای کاربر ایجاد شد توسط سیستم لغو گردید .'
                ]);
                //create session
                if ($factor_item->factoritemable->session == 1) {
                    Session::createSessionPendingMainInvoice($factor_item->id);
                }
            }
            $user = $totalFactorItem->factor->user;
            $item = self::setItem($totalFactorItem);
            $resnumber = TotalFactorItem::setFatorItemCollectionResnumber($totalFactorItem);
            $contract_number = TotalFactorItem::setAgentContractNumber($totalFactorItem);
            if ($totalFactorItem->factorItems->where('factoritemable_id', 124)->count()) {
                $contract_view = 'Admin.totalfactoritem.layouts.contractLoanCash';
            } elseif ($totalFactorItem->factorItems->where('factoritemable_id', 17)->count()) {
                $contract_view = 'Admin.totalfactoritem.layouts.contractGhorfeOnline';
            } else {
                $contract_view = 'Admin.totalfactoritem.layouts.contract';
            }
            $content = View::make($contract_view, [
                'resnumber' => $resnumber,
                'contract_number' => $contract_number,
                'totalFactorItem' => $totalFactorItem,
                'user' => $user,
                'item' => $item,
            ])->render();
            ContractView::create([
                'contract' => $content,
                'contract_type' => TotalFactorItem::class,
                'contract_id' => $totalFactorItem->id
            ]);
            $totalFactorItem->update(['credit' => $totalFactorItem->total_price + $totalFactorItem->credit, 'confirm' => true]);
        }

        TotalFactorItem::createOrUpdateInstallments($totalFactorItem->approval_date, $totalFactorItem);

        return redirect()->back()->with([
            'update' => 'success'
        ]);
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     * upload original or basic financial document
     */
    public function uploadDocument(Request $request)
    {
        $total_factor_item = TotalFactorItem::find($request->item['id']);
        if ($total_factor_item->confirm) {
            return redirect()->back()->with([
                'message' => 'این پرونده قبلا توسط مدیر تایید شده است.'
            ]);
        }

        $total_factor_item->update([
            'guarantor_checked' => $request->guarantor_checked ? 1 : 0,
            'file_number_mali' => $request->file_number_mali,
            'total_factor_item_status_id' => $request->status_factor ?? $total_factor_item->total_factor_item_status_id,
        ]);
        $user = User::find($request->user_id);
        if ($request->urlimgmeli) {
            Helper::deleteImage($user->imgmeli);
            $user->update(['imgmeli' => Helper::moveImage($request->urlimgmeli, 'document')]);
        }

        if ($request->urlimgshenasname) {
            Helper::deleteImage($user->imgshenasname);
            $user->update(['imgshenasname' => Helper::moveImage($request->urlimgshenasname, 'document')]);
        }

        if ($request->urlimgturnover) {
            Helper::deleteImage($user->imgturnover);
            $user->update(['imgturnover' => Helper::moveImage($request->urlimgturnover, 'document')]);
        }

        $input = $request->user;

        if ($input) {
            $user->update($input);
        }

        self::formData($request->item);

        return redirect()->back()->with([
            'totalFactorItem_update' => true,
            'send' => true
        ]);
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     * suspention totalFactorItem in supporter card
     */
    public function suspension(Request $request)
    {

        $this->validate($request, [
            'type' => "required",
            'date' => 'required_if:type,==,support',
            'description' => 'required',
        ]);
        if ($request->type == 'execution') {

            $total_factor_item = TotalFactorItem::find($request->id);
            if (!$total_factor_item->confirm) {
                $total_factor_item->update([
                    'total_factor_item_status_id' => 19,
                ]);
            }

            foreach ($total_factor_item->factorItems as $factor_item) {
                $fator_item_collections = FactorItem::getFatorItemCollection($factor_item);

                $fator_item_collections->update([
                    'status_factor' => 19,
                ]);
            }

            $total_factor_item->factor->comments()->create([
                'user_id' => auth()->user()->id,
                'comment' => nl2br($request->description),
            ]);
        } elseif ($request->type == 'supporter') {
            $total_factor_item = TotalFactorItem::find($request->id);
            if (!$total_factor_item->confirm) {
                $total_factor_item->update([
                    'total_factor_item_status_id' => 3,
                    'expired_at' => Helper::vertaToMiladi($request->date),
                ]);
            }
            foreach ($total_factor_item->factorItems as $factor_item) {
                $fator_item_collections = FactorItem::getFatorItemCollection($factor_item);
                $fator_item_collections->update([
                    'status_factor' => 3,
                    'expired_at' => Helper::vertaToMiladi($request->date),
                ]);
            }
            $total_factor_item->factor->comments()->create([
                'user_id' => auth()->user()->id,
                'comment' => nl2br($request->description),
            ]);
        }


        if ($request->reason == 1) {
            event(new smsNotAnswer($total_factor_item->factor->user, auth()->user(), 'پشتیبانی'));
        }

        return redirect()->back()->with([
            'successByMessage' => 'پیام پیگیری شما با موفقیت ثبت شد.'
        ]);
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     * registration of settlement request
     */
    public function requestContractSettlement(Request $request)
    {
        $total_factor_item = TotalFactorItem::find($request->total_factor_item_id);
        if (!in_array($total_factor_item->status_mali, [7, 6, 8, 9])) {
            $applicant_id = auth()->user()->id;
            $total_factor_item_contract_settlement = $total_factor_item->totalFactorItemContractSettlement()->create([
                'applicant_id' => $applicant_id,
                'description_request_contract_settlement' => $request->description_request_contract_settlement,
            ]);
            if ($request->urlrequest_contract_settlement) {
                Helper::deleteImage($total_factor_item_contract_settlement->img_request_contract_settlement);
                $total_factor_item_contract_settlement->update(['img_request_contract_settlement' => Helper::moveImage($request->urlrequest_contract_settlement, 'document')]);
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
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     * registration of contract settlement
     */
    public function contractSettlement(Request $request)
    {
        $total_factor_item = TotalFactorItem::find($request->item['id']);
        if ($total_factor_item->status_mali == 9) {
            $total_factor_item->totalFactorItemContractSettlement->update([
                'deliverer_id' => auth()->user()->id,
                'img_warranty_delivery_receipt' => $request->img_warranty_delivery_receipt,
            ]);
            if ($request->urlimg_warranty_delivery_receipt) {
                Helper::deleteImage($total_factor_item->totalFactorItemContractSettlement->img_warranty_delivery_receipt);
                $total_factor_item->totalFactorItemContractSettlement->update(['img_warranty_delivery_receipt' => Helper::moveImage($request->urlimg_warranty_delivery_receipt, 'document')]);
            }
            $total_factor_item->totalFactorItemInstallments()->where('status', 0)->update(['status' => 2, 'pay_date' => now()]);

            $total_factor_item->update([
                'status_mali' => 6,
            ]);

            return redirect()->back()->with([
                'successByMessage' => 'تسویه حساب پرونده با موفقیت ثبت شد.'
            ]);
        }
        return redirect()->back()->with([
            'failByMessage' => 'این پرونده آماده تسویه حساب نمی باشد.'
        ]);
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     * change totalFactorItem status
     */
    public function changeStatus(Request $request)
    {
        $item = $request->item;
        $total_factor_item = TotalFactorItem::find($item['id']);
        //check confirmation totalFactorItem
        if ($total_factor_item->confirm) {
            return redirect()->back()->with([
                'failByMessage' => 'این پرونده قبلا تایید شده است امکان تغییر وضعیت اجرایی پرونده وجود ندارد.'
            ]);
        }
        $factor_items = $total_factor_item->factorItems;
        $total_factor_item->update([
            'total_factor_item_status_id' => $request->status_factor,
        ]);
        foreach ($factor_items as $factor_item) {
            $fator_item_collections = FactorItem::getFatorItemCollection($factor_item);

            $fator_item_collections->update([
                'status_factor' => $request->status_factor
            ]);
        }
        return redirect()->back()->with([
            'update' => 'success'
        ]);
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     * change totalFactorItem status_mali
     */
    public function changeStatusMaliFactor(Request $request)
    {
        $total_factor_item = TotalFactorItem::find($request->id);
        $factor_items = $total_factor_item->factorItems;
        $total_factor_item->update([
            'status_mali' => $request->status_mali,
        ]);

        return redirect()->back()->with([
            'update' => 'success'
        ]);
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     * update financial calculattions totalFactorItem
     */
    public function updateCalculate(Request $request)
    {
        $total_factor_item = TotalFactorItem::find($request->input('id'));

        if ($total_factor_item->confirm) {
            return redirect()->back()->with([
                'failByMessage' => 'این پرونده تایید شده است شما مجاز به انجام این کار نیستید.'
            ]);
        }
        $percent_fines = isset($request->percent_fines) ? $request->percent_fines : $total_factor_item->percent_fines;
        $prepayment = array();

        $prepayment['prepayment_num'] = $request->input('prepayment_num');
        $prepayment['prepayment_count'] = $request->input('prepayment_num') - $total_factor_item->property->prepayment_num - $total_factor_item->property_prepayment_count;

        $total_factor_item->update([
            'guarantee_type' => $request->guarantee_type ? $request->guarantee_type : $total_factor_item->guarantee_type,
            'percent_fines' => $percent_fines,
        ]);
        $total_factor_item->property->update([
            'prepayment_num' => $prepayment['prepayment_num'],
            'prepayment_count' => $prepayment['prepayment_count']
        ]);

        //for change approval date
        if ($request->created_at) {
            $approval_date = Helper::vertaToMiladi($request->created_at);
            TotalFactorItem::createOrUpdateInstallments($approval_date, $total_factor_item);
        }

        return redirect()->back()->with([
            'update' => 'success'
        ]);
    }

    /**
     * @param mixed $item
     * @return void
     * save the financial formItems of the totalFactorItem
     */
    private function formData($item)
    {
        $total_factor_item = TotalFactorItem::find($item['id']);
        $contract_values = $total_factor_item->contract_values ? $total_factor_item->contract_values : [];
        foreach (ContractForm::where('value', $total_factor_item->guarantee_type)->orWhere('value', 'all')->get() as $contract_form) {
            if ($contract_form->type == 'other_document_image') {
                if (isset($item[$contract_form->name])) {
                    $images = isset($contract_values[$contract_form->name]) ? $contract_values[$contract_form->name] : [];
                    $files = $item['url' . $contract_form->name];
                    foreach ($files as $file) {
                        if ($file) {
                            if (isset($images)) {
                                $url = Helper::moveImage($file, 'document');
                                $images[] = $url;
                            }
                        }
                    }
                    if (isset($contract_values[$contract_form->name])) {
                        $contract_values[$contract_form->name] = $images;
                    }
                    $contract_values = Arr::add($contract_values, $contract_form->name, $images);
                }
            }
            if ($contract_form->type == 'file') {
                if (isset($item[$contract_form->name])) {
                    $img = $item[$contract_form->name];
                    if ($img) {
                        $url = Helper::moveImage($item['url' . $contract_form->name], 'document');
                        if (isset($contract_values[$contract_form->name])) {
                            $contract_values[$contract_form->name] != $url ? Helper::deleteImage($contract_values[$contract_form->name]) : '';
                            $contract_values[$contract_form->name] = $url;
                        }
                        $contract_values = Arr::add($contract_values, $contract_form->name, $url);
                    }
                    unset($item[$contract_form->name]);
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
            $total_factor_item->update([
                'contract_values' => !is_null($contract_values) ? $contract_values : null,
            ]);
        }
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     * send link and pay the registeration || prepayment(title == prepayment || title == registeration_debt)
     */
    public function payment(Request $request)
    {
        $totalFactorItem = TotalFactorItem::find($request->input('id'));
        $user = User::find($request->input('user_id'));
        $prepayment = array();
        $prepayment['price'] = $totalFactorItem->property->prepayment_price;
        $prepayment['debt'] = $totalFactorItem->property->prepayment_debt;
        $prepayment['count'] = $totalFactorItem->property->prepayment_count;
        $prepayment['num'] = $totalFactorItem->property->prepayment_num;
        $price = (float)str_replace(',', '', Helper::numberConverter($request->input('price')));
        if ($request->input('payment_type') == 'credit') {
            $result = User::checkCredit($user, $price, $request->input('password'), $request->input('title'));
            if (!$result['success']) {
                return redirect()->back()->with([
                    'failByMessage' => $result['message']
                ]);
            }
        }
        if ($request->input('payment_type') == 'wallet') {
            $result = User::checkWallet($user, $price, $request->input('password'));
            if (!$result['success']) {
                return redirect()->back()->with([
                    'failByMessage' => $result['message']
                ]);
            }
        }
        $factor = new Factor();
        $factor->title = $request->input('title');
        $factor->user_id = $request->input('user_id');
        $factor->price = $price;
        $factor->level = $prepayment['num'] - $prepayment['count'] + 1;
        $factor->type = $request->input('payment_type');
        $factor->total_factor_item_id = $request->input('id');
        $factor->callcenter_id = auth()->user()->id;
        $factor->number_card = $request->input('number_card');
        $factor->payment_code = $request->input('payment_code');
        $factor->representation_id = auth()->user()->admin_representation_id;
        $factor->center_id = auth()->user()->center_id;
        $factor->status = $request->input('payment_type') == 'online' || $request->input('payment_type') == 'send_tax_free_card_number' ? 0 : 1;
        $factor->expired_at = now()->addDay(3);
        $factor->created_at = Helper::vertaToMiladi($request->payment_date);
        $factor->save();
        //upload bill_image cash factor
        if ($request->url_bill_image) {
            $factor->update(['bill_image' => Helper::moveImage($request->url_bill_image, 'document')]);
        }
        //send sms for pay factor
        if ($request->input('payment_type') == 'online') {
            event(new sendlinkFactor($user, $factor, auth()->user()->family, 'online'));
        }
        elseif($request->input('payment_type') == 'send_tax_free_card_number')
        {
            event(new sendlinkFactor($user, $factor, auth()->user()->family, 'tax_free_card'));
        }
        else {
            Factor::savePayment($factor);
        }
        return redirect()->back()->with([
            'successByMessage' => 'با موفقیت انجام شد'
        ]);
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     * send initial link whitout total price
     */
    public function sendInitialLink(Request $request)
    {
        //calculate
        $total_factor_item = TotalFactorItem::find($request->id);
        $user = $total_factor_item->factor->user;
        $factor_items = $total_factor_item->factorItems;
        $price = (float)str_replace(',', '', Helper::numberConverter($request->input('price')));
        foreach ($factor_items as $factor_item) {
            $prepayment = [];
            $factor_item_able = $factor_item->factoritemable;
            $total_price = $factor_item->total_price ? $factor_item->total_price : $factor_item_able->installments->min('down_interval');
            $total_price = $total_price + $factor_item->discount;
            //calculate
            FactorItem::calculate($factor_item, $total_price, $factor_item->type, $user);
        }
        TotalFactorItem::updateCalculate($total_factor_item);
        $user = User::find($request->input('user_id'));
        $prepayment['num'] = $factor_item->property->prepayment_num;
        $prepayment['count'] = $factor_item->property->prepayment_count;

        $factor = new Factor();
        $factor->title = 'prepayment';
        $factor->user_id = $request->input('user_id');
        $factor->price = $price;
        $factor->level = $prepayment['num'] - $prepayment['count'] + 1;
        $factor->type = $request->input('payment_type');
        $factor->total_factor_item_id = $request->input('id');
        $factor->callcenter_id = auth()->user()->id;
        $factor->representation_id = auth()->user()->admin_representation_id;
        $factor->center_id = $factor_item->center_id;
        $factor->expired_at = now()->addDay(3);
        $factor->save();
        if($request->input('payment_type') == 'online')
        {
            event(new sendlinkFactor($user, $factor, auth()->user()->family, 'online'));
        }
        elseif($request->input('payment_type') == 'send_tax_free_card_number')
        {
            event(new sendlinkFactor($user, $factor, auth()->user()->family, 'tax_free_card'));
        }
        return redirect()->back()->with([
            'send' => true
        ]);
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     * send link and pay installment factor
     */
    public function saveInstallmentsPaid(Request $request)
    {
        $user = User::find($request->input('user_id'));
        $total_factor_item = TotalFactorItem::find($request->id);
        TotalFactorItem::createOrUpdateInstallments($total_factor_item->approval_date, $total_factor_item);

        for ($i = 0; $i < ($request->price ? count($request->price) : 0); $i++) {
            $price = (float)str_replace(',', '', Helper::numberConverter($request->price[$i]));

            if ($request->payment_type[$i] == 'credit') {
                $result = User::checkCredit($user, $price, $request->input('password'), 'installment');
                if (!$result['success']) {
                    return redirect()->back()->with([
                        'failByMessage' => $result['message']
                    ]);
                }
            }
            if ($request->payment_type[$i] == 'wallet') {
                $result = User::checkWallet($user, $price, $request->input('password'));
                if (!$result['success']) {
                    return redirect()->back()->with([
                        'failByMessage' => $result['message']
                    ]);
                }
            }
            if ($request->payment_type[$i] == 'deduction_price_difference') {
                $result = TotalFactorItem::checkPriceDifference($total_factor_item, $price);
                if (!$result['success']) {
                    return redirect()->back()->with([
                        'failByMessage' => $result['message']
                    ]);
                }
            }
            $factor_item = $request->factor_item;
            $factor = new Factor();
            $factor->title = 'installment';
            $factor->user_id = $total_factor_item->factor->user->id;
            $factor->price = $price;
            $factor->level = $i + 1;
            $factor->type = $request->payment_type[$i];
            $factor->total_factor_item_id = $total_factor_item->id;
            $factor->factor_item_id = isset($factor_item) ? $factor_item : null;
            $factor->callcenter_id = auth()->user()->id;
            $factor->representation_id = auth()->user()->admin_representation_id;
            $factor->center_id = auth()->user()->center_id;
            $factor->status = $request->payment_type[$i] == 'online' || $request->payment_type[$i] == 'send_tax_free_card_number' ? 0 : 1;
            $factor->number_card = $request->number_card[$i];
            $factor->payment_code = $request->payment_code[$i];
            $factor->created_at = Helper::vertaToMiladi($request->payment_date[$i]);
            $factor->expired_at = now()->addDay(3);
            $factor->save();
            //upload bill_image cash factor
            if ($request->url_bill_image) {
                $factor->update(['bill_image' => Helper::moveImage($request->url_bill_image, 'document')]);
            }
            //send sms if the type of payment is online for payment factor
            if ($request->payment_type[$i] == 'online') {
                event(new sendlinkFactor($user, $factor, auth()->user()->family, 'online'));
            }
            elseif($request->payment_type[$i] == 'send_tax_free_card_number')
            {
                event(new sendlinkFactor($user, $factor, auth()->user()->family, 'tax_free_card'));
            }
            else {
                Factor::savePayment($factor);
            }
        }

        return redirect()->back()->with([
            'update' => 'success'
        ]);
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     * send link and pay extrapay factor(title == extra_pay)
     */
    public function saveExtraPay(Request $request)
    {
        $total_factor_item = TotalFactorItem::find($request->id);
        TotalFactorItem::createOrUpdateInstallments($total_factor_item->approval_date, $total_factor_item);
        $user = User::find($request->input('user_id'));
        $price = (float)str_replace(',', '', Helper::numberConverter($request->input('price')));
        //pay factor from user credit
        if ($request->input('payment_type') == 'credit') {
            $result = User::checkCredit($user, $price, $request->input('password'), 'extra_pay');
            if (!$result['success']) {
                return redirect()->back()->with([
                    'failByMessage' => $result['message']
                ]);
            }
        }
        //pay factor from user wallet
        if ($request->input('payment_type') == 'wallet') {
            $result = User::checkWallet($user, $price, $request->input('password'));
            if (!$result['success']) {
                return redirect()->back()->with([
                    'mfailByMessageessage' => $result['message']
                ]);
            }
        }
        //pay factor from file price difference
        if ($request->input('payment_type') == 'deduction_price_difference') {
            $result = TotalFactorItem::checkPriceDifference($total_factor_item, $price);
            if (!$result['success']) {
                return redirect()->back()->with([
                    'failByMessage' => $result['message']
                ]);
            }
        }
        //pay factor from file overpayment
        if ($request->input('payment_type') == 'overpayment') {
            $result = TotalFactorItem::checkOverpayment($total_factor_item, $price);
            if (!$result['success']) {
                return redirect()->back()->with([
                    'failByMessage' => $result['message']
                ]);
            }
        }
        //ceate and send factor
        $factor_item = $request->factor_item;
        $factor = new Factor();
        $factor->title = 'extra_pay';
        $factor->user_id = $total_factor_item->factor->user->id;
        $factor->price = $price;
        $factor->level = 1;
        $factor->type = $request->payment_type;
        $factor->total_factor_item_id = $total_factor_item->id;
        $factor->factor_item_id = isset($factor_item) ? $factor_item : null;
        $factor->callcenter_id = auth()->user()->id;
        $factor->representation_id = auth()->user()->admin_representation_id;
        $factor->status = $request->payment_type == 'online' ? 0 : 1;
        $factor->number_card = $request->number_card;
        $factor->payment_code = $request->payment_code;
        $factor->expired_at = now()->addDay(1);
        $factor->created_at = Helper::vertaToMiladi($request->payment_date);
        $factor->save();
        //upload bill_image cash factor if the type of payment is cash
        if ($request->url_bill_image) {
            $factor->update(['bill_image' => Helper::moveImage($request->url_bill_image, 'document')]);
        }
        //send sms if the type of payment is online for payment factor
        if ($request->payment_type == 'online') {
            event(new sendlinkFactor($user, $factor, auth()->user()->family, 'online'));
        }
        elseif($request->payment_type == 'send_tax_free_card_number')
        {
            event(new sendlinkFactor($user, $factor, auth()->user()->family, 'tax_free_card'));
        }
        return redirect()->back()->with([
            'update' => 'success'
        ]);
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     * save return payment prepayment or price diffrenece
     */
    public function returnPayment(Request $request)
    {
        $total_factor_item = TotalFactorItem::find($request->input('id'));
        $prepayment['prepayment_price'] = $total_factor_item->property->prepayment_price;
        $prepayment['prepayment_debt'] = $total_factor_item->property->prepayment_debt;
        $prepayment['prepayment_count'] = $total_factor_item->property->prepayment_count;
        $prepayment['prepayment_num'] = $total_factor_item->property->prepayment_num;
        $price_difference['price_difference_installment'] = $total_factor_item->property->price_difference_installment;
        $price_difference['price_difference_total'] = $total_factor_item->property->price_difference_total;
        $price_difference['price_difference_debt'] = $total_factor_item->property->price_difference_debt;
        $price = (float)str_replace(',', '', Helper::numberConverter($request->input('price')));

        //return payment from prepayment price paid
        if ($request->title == 'prepayment') {
            if ($prepayment['prepayment_debt'] < 0) {
                $prepayment['prepayment_debt'] = $prepayment['prepayment_debt'] + $price;
                $total_factor_item->property->update([
                    'prepayment_debt' => $prepayment['prepayment_debt']
                ]);
            } else {
                return redirect()->back()->with([
                    'failByMessage' => ' پرداختی مصرف نشده این پرونده کمتر از ' . number_format($price) . 'می باشد.'
                ]);
            }
        }

        //return payment from price_difference price paid
        if ($request->title == 'price_difference') {
            $result = TotalFactorItem::checkPriceDifference($total_factor_item, $price);
            if (!$result['success']) {
                return redirect()->back()->with([
                    'failByMessage' => $result['message']
                ]);
            }
        }
        $factor_item = $request->factor_item;
        $factor = new Factor();
        $factor->title = $request->title;
        $factor->user_id = $request->input('user_id');
        $factor->price = $price;
        $factor->type = $request->input('payment_type');
        $factor->total_factor_item_id = $request->input('id');
        $factor->factor_item_id = isset($factor_item) ? $factor_item : null;
        $factor->callcenter_id = auth()->user()->id;
        $factor->number_card = $request->input('number_card');
        $factor->payment_code = $request->input('payment_code');
        $factor->representation_id = auth()->user()->admin_representation_id;
        $factor->status = 2;
        $factor->created_at = Helper::vertaToMiladi($request->payment_date);
        $factor->save();
        //increase credit user
        if ($factor->type == 'increase_credit') {
            $factor->user->update(['credit' => $factor->user->credit + $factor->price]);
        }
        //upload bill_image cash factor
        if ($request->url_bill_image) {
            $factor->update(['bill_image' => $request->url_bill_image]);
        }
        return redirect()->back()->with([
            'update' => 'success'
        ]);
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     * closing the factorItem
     */
    public function close(Request $request)
    {
        $this->validate($request, [
            'comment' => 'required|string',
        ]);

        $total_factor_item = TotalFactorItem::find($request->id);
        $check_payment = TotalFactorItem::checkPayment($total_factor_item);

        if ($check_payment) {
            return redirect()->back()->with([
                'failByMessage' => ' به دلیل وجود پرداختی برای این پرونده ، امکان مختومه نمودن وجود ندارد .'
            ]);
        }

        $total_factor_item->update(['total_factor_item_status_id' => 15]);
        foreach ($total_factor_item->factorItems as $factor_item) {
            $fator_item_collections = FactorItem::getFatorItemCollection($factor_item);
            event(new factorItemClosing($factor_item));

            $fator_item_collections->update([
                'status_factor' => 15
            ]);
        }
        if ($total_factor_item->confirm) {
            return redirect()->back()->with([
                'message' => 'این پرونده تایید شده است شما مجاز به انجام این کار نیستید لطفا تسویه حساب پرونده را بزنید.'
            ]);
        }

        if ($request->comment) {
            $total_factor_item->factor->comments()->create([
                'user_id' => auth()->user()->id,
                'comment' => $request->comment,
            ]);
        }

        return redirect()->back()->with([
            'update' => 'success'
        ]);
    }

    /**
     * @param mixed $item
     * @return string //price
     */
    private function getGuaranteePrice($item)
    {
        $guarantee_price = number_format($item['loan'] * (140 / 100));

        return $guarantee_price;
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeImpunity(Request $request)
    {
        if (!auth()->user()->isAbleTo('store-impunity')) {
            abort(403);
        }
        //request validation
        $request['impunity_amount'] = Helper::numberConverter($request->impunity_amount);

        $request->validate([
            'impunity_amount' => ['required', 'regex:/\d{1,3}(,\d{3})*(\.\d+)?$/'],
            'impunity_type' => ['required', Rule::in('installment', 'penalty')],
            'description' => ['nullable'],
        ]);

        $total_factor_item = TotalFactorItem::find($request->total_factor_item_id);

        //deActive old impunity
        $total_factor_item->impunities()->where('status', 1)->where('type', $request->impunity_type)->update([
            'status' => 0
        ]);
        //store new impunity
        $data = array(
            'amount' => str_replace(',', '', $request->impunity_amount),
            'type' => $request->impunity_type,
            'description' => $request->description,
            'created_by' => auth()->user()->id,
        );
        $impunity = $total_factor_item->impunities()->create($data);

        //store comment for factor of total_factor_item
        $factor_comment = "بخشودگی به مبلغ {$request->impunity_amount} ریال با شناسه {$impunity->id} ثبت گردید ";
        if ($request->description)
            $factor_comment .= "| توضیح بخشودگی : {$request->description}";
        $total_factor_item->factor->comments()->create([
            'user_id' => auth()->user()->id,
            'comment' => $factor_comment
        ]);

        //return response
        alert()->success('بخشودگی با موفقیت ثبت گردید ', 'عملیات موفق ');
        return redirect()->back();
    }

    /**
     * @param Impunity $impunity
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Exception
     */
    public function destroyImpunity(Impunity $impunity)
    {
        if (!auth()->user()->isAbleTo('delete-impunity')) {
            abort(403);
        }
        //store comment for factor of total_factor_item
        $factor_comment = "بخشودگی با شناسه {$impunity->id} حذف گردید .";
        $impunity->impunitiable->factor->comments()->create([
            'user_id' => auth()->user()->id,
            'comment' => $factor_comment
        ]);
        //delete impunity
        $impunity->delete();
        //return response
        alert()->success('بخشودگی با موفقیت حذف گردید ', 'عملیات موفق ');
        return redirect()->back();
    }

    /**
     * @param TotalFactorItem $totalFactorItem
     * @return mixed
     */
    private function fetchInstallments(TotalFactorItem $totalFactorItem)
    {
        $installments = $totalFactorItem->totalFactorItemInstallments->sortBy('date');
        $installments = new TotalFactorItemInstallmentCollection($installments, $installments);
        $installments = json_encode($installments, true);
        $installments = json_decode($installments, true);
        return $installments['data'];
    }

    /**
     * @param TotalFactorItem $totalFactorItem
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function checks(TotalFactorItem $totalFactorItem)
    {
        $checks = $totalFactorItem->checks;
        $user = $totalFactorItem->factor->user;
        $factor = $totalFactorItem->factor;
        return view('Admin.totalfactoritem.checks', compact('checks', 'user', 'factor', 'totalFactorItem'));
    }

    /**
     * @param TotalFactorItem $totalFactorItem
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function promissories(TotalFactorItem $totalFactorItem)
    {
        $promissories = $totalFactorItem->promissories;
        $user = $totalFactorItem->factor->user;
        $factor = $totalFactorItem->factor;
        return view('Admin.totalfactoritem.promissories', compact('promissories', 'user', 'factor', 'totalFactorItem'));
    }


}
