<?php

    namespace App\Http\Controllers\Admin;
    use App\Exports\DebtorUsersExport;
    use App\Helpers\Helper;
    use App\Http\Controllers\Controller;
    use App\Http\Resources\DebtorCollection;
    use App\Http\Resources\TotalFactorItemInstallmentCollection;
    use App\Models\ContractForm;
    use App\Models\FactorItem;
    use App\Models\FactorItemAttributes;
    use App\Models\TotalFactorItem;
    use App\Models\User;
    use Carbon\Carbon;

    class AccountingController extends  Controller
    {
        /**
         * @return \Illuminate\Contracts\View\Factory|\Illuminate\Foundation\Application|\Illuminate\View\View
         */
        public function index()
        {
            return view('Admin.accounting.all');
        }
        /**
         * @return DebtorCollection|\Illuminate\Http\Response|\Symfony\Component\HttpFoundation\BinaryFileResponse
         */
        public function filterCashDebtors()
        {
            $name = request('name');
            $family = request('family');
            $phone = request('phone');
            $start_amount = (float)str_replace(',', '', Helper::numberConverter(request('start_amount')));
            $end_amount = (float)str_replace(',', '', Helper::numberConverter(request('end_amount')));

            $usersQuery=User::query();

            if (isset($name) && trim($name) != '') {
                $usersQuery->where('name','like','%'.$name.'%');
            }

            if (isset($family) && trim($family) != '') {
                $usersQuery->where('family','like','%'.$family.'%');
            }
            if (isset($phone) && trim($phone) != '') {
                $usersQuery->where('phone',$phone);
            }

            $usersQuery->whereHas('factors',function ($usersQuery){
                $usersQuery->where('status', 1);
                $usersQuery->whereHas('factorItems',function ($usersQuery){
                    if (!is_null(request('center')) && request('center') != "all" ){
                        $usersQuery->where('center_id',request('center'));
                    }
                    $usersQuery->whereNotIn('status_factor', [15]);
                    if (!is_null(auth()->user()->center_id)) {
                        $usersQuery->where('center_id',auth()->user()->center_id);
                    }
                    if (!is_null(auth()->user()->admin_representation_id) || !is_null(auth()->user()->admin_organization_id) ){
                        $usersQuery->whereHas('factor',function ($usersQuery){
                            if (!is_null(auth()->user()->admin_representation_id)){
                                $usersQuery->where('representation_id', auth()->user()->admin_reperesetation_id);
                            }
                            if (!is_null(auth()->user()->admin_organization_id)){
                                $usersQuery->where('organization_id', auth()->user()->admin_organization_id);
                            }
                        });
                    }
                    if ( request('by_amount')=='on' && request('status')=='all'){
                        $usersQuery->whereHas('property',function ($usersQuery , $start_amount , $end_amount){
                            $usersQuery->whereRaw('prepayment_debt >'. $start_amount)
                                ->whereRaw('(prepayment_debt <'. $end_amount);
                        });
                    }
                    else{
                        $usersQuery->whereHas('property',function ($usersQuery){
                            $usersQuery->where('prepayment_debt','>',0)->whereRaw('prepayment_price > prepayment_debt');
                        });
                    }
                });
            });


            if (isset(request()->excel) && request()->excel == 'on') {
                return (new DebtorUsersExport($usersQuery))->download('debtors.xlsx');
            }
            $users=$usersQuery->paginate(20);

            return  new DebtorCollection($users);
        }

        /**
         * @return DebtorCollection|\Illuminate\Http\Response|\Symfony\Component\HttpFoundation\BinaryFileResponse
         */
        public function filterInstallmentDebtors()
        {
            $name = request('name');
            $family = request('family');
            $phone = request('phone');
            $start_amount = (float)str_replace(',', '', Helper::numberConverter(request('start_amount')));
            $end_amount = (float)str_replace(',', '', Helper::numberConverter(request('end_amount')));

    //        if (!Cache::has('total_debt_except_penalty')){
    //            $debt=FactorItem::fetchSystemDebtExceptPenalty();
    //            cache(['total_debt_except_penalty' => $debt], now()->addDays(1));
    //        }

            $usersQuery=User::query();

            if (isset($name) && trim($name) != '') {
                $usersQuery->where('name','like','%'.$name.'%');
            }

            if (isset($family) && trim($family) != '') {
                $usersQuery->where('family','like','%'.$family.'%');
            }
            if (isset($phone) && trim($phone) != '') {
                $usersQuery->where('phone',$phone);
            }

            if (!is_null(request('center')) && request('center') != "all" ){
                $usersQuery->whereHas('factors',function ($query){
                    $query->whereHas('factorItem',function ($query){
                        $query->where('center_id',request('center'));
                    });
                });
            }

            $usersQuery->whereHas('factors',function ($usersQuery){
                $usersQuery->whereHas('totalFactorItem',function ($usersQuery){
    //                $query->whereNotIn('total_factor_item_status_id', [15]);
    //                if (!is_null(auth()->user()->center_id)) {
    //                    $query->where('center_id',auth()->user()->center_id);
    //                }
                    $usersQuery->whereHas('factor',function ($usersQuery){
                        $usersQuery->where('status', 1);
                        if (!is_null(auth()->user()->admin_representation_id)){
                            $usersQuery->where('representation_id', auth()->user()->admin_reperesetation_id);
                        }
                        if (!is_null(auth()->user()->admin_organization_id)){
                            $usersQuery->where('organization_id', auth()->user()->admin_organization_id);
                        }
                    });

                    if ( request('by_amount')=='on' && request('status')=='all'){
                        $usersQuery->whereHas('property',function ($usersQuery , $start_amount , $end_amount){
                            $usersQuery->whereRaw('(prepayment_debt + price_difference_debt) >'. $start_amount)
                                ->whereRaw('(prepayment_debt + price_difference_debt) <'. $end_amount);
                        });
                    }
                    if (request()->status == 1  ){
                        if (request('by_amount') == 'on'){
                            $usersQuery->whereHas('property',function ($usersQuery , $start_amount , $end_amount){
                                $usersQuery->whereRaw('prepayment_price > prepayment_debt')->where( 'prepayment_debt', '>', $start_amount)
                                    ->where( 'prepayment_debt', '<', $end_amount);
                            });
                        }else {
                            $usersQuery->whereHas('property', function ($usersQuery) {
                                $usersQuery->whereRaw('prepayment_price > prepayment_debt')->where('prepayment_debt', '>', 0);
                            });
                        }
                    }
                    if (request()->status == 4 ){
                        $usersQuery->where('type',0);
                        if (request('by_amount') == 'on'){
                            $usersQuery->whereHas('property',function ($usersQuery , $start_amount , $end_amount){
                                $usersQuery->whereRaw('prepayment_price > prepayment_debt')->where( 'prepayment_debt', '>', $start_amount)
                                    ->where( 'prepayment_debt', '<', $end_amount);
                            });
                        }else {
                            $usersQuery->whereHas('property', function ($usersQuery) {
                                $usersQuery->whereRaw('prepayment_price > prepayment_debt')->where('prepayment_debt', '>', 0);
                            });
                        }
                    }
                    if (request()->status == 2  ){
                        if (request('by_amount') == 'on'){
                            $usersQuery->whereHas('property',function ($usersQuery , $start_amount , $end_amount){
                                $usersQuery->where( 'price_difference_debt', '>', $start_amount)
                                    ->where( 'price_difference_debt', '<', $end_amount);
                            });
                        }else{
                            $usersQuery->whereHas('property',function ($usersQuery){
                                $usersQuery->where( 'price_difference_debt', '>', 0);
                            });
                        }
                    }
                    if (request()->status == 3 && request('by_amount') != 'on'){
                        $usersQuery->whereHas('totalFactorItemInstallments',function ($usersQuery){
                            $usersQuery->where('status', 0)->where('date', '<', Carbon::now())->whereHas('totalFactorItem',function ($usersQuery){
                                $usersQuery->where('confirm',1);
                            });
                        });
                    }

                    if (request()->status =='all' && request('by_amount') != 'on'){
                        $usersQuery->whereHas('property',function ($usersQuery){
                            $usersQuery->whereRaw('prepayment_price > prepayment_debt')->where('prepayment_debt','>',0)->orWhere('price_difference_debt','>',0);
                        });

                        $usersQuery->orWhere('confirm',1)->whereHas('totalFactorItemInstallments',function ($usersQuery){
                            $usersQuery->where('status', 0)->where('date', '<', Carbon::now());
                        });
                    }
                });
            })->latest();

            if (isset(request()->excel) && request()->excel == 'on') {
                return (new DebtorUsersExport($usersQuery))->download('debtors.xlsx');
            }
            $users=$usersQuery->paginate(10);

            return  new DebtorCollection($users);
        }
    /**
         * @return DebtorCollection|\Illuminate\Http\Response|\Symfony\Component\HttpFoundation\BinaryFileResponse
         */
        public function filterCreditDebt()
        {
            $name = request('name');
            $family = request('family');
            $phone = request('phone');
            $start_amount = (float)str_replace(',', '', Helper::numberConverter(request('start_amount')));
            $end_amount = (float)str_replace(',', '', Helper::numberConverter(request('end_amount')));

    //        if (!Cache::has('total_debt_except_penalty')){
    //            $debt=FactorItem::fetchSystemDebtExceptPenalty();
    //            cache(['total_debt_except_penalty' => $debt], now()->addDays(1));
    //        }

            $usersQuery=User::query();

            if (isset($name) && trim($name) != '') {
                $usersQuery->where('name','like','%'.$name.'%');
            }

            if (isset($family) && trim($family) != '') {
                $usersQuery->where('family','like','%'.$family.'%');
            }
            if (isset($phone) && trim($phone) != '') {
                $usersQuery->where('phone',$phone);
            }
            if (!is_null(request('center')) && request('center') != "all" ){
                $usersQuery->whereHas('factors',function ($query){
                    $query->whereHas('factorItem',function ($query){
                        $query->where('center_id',request('center'));
                    });
                });
            }

            if (request()->creditType == 'creditUser' ){
                if (request('by_amount') == 'on'){
                        $usersQuery->where( 'credit', '>', $start_amount)
                            ->where( 'credit', '<', $end_amount);
                }else {
                        $usersQuery->where('credit', '>', 0);
                }
            }
            if (request()->creditType == 'walltUser' ){
                if (request('by_amount') == 'on'){
                        $usersQuery->where( 'wallet', '>', $start_amount)
                            ->where( 'wallet', '<', $end_amount);
                }else {
                    $usersQuery->where('wallet' , '>' , 0);
                }
            }
            if (request()->creditType == 'factorItem' ){
                if (request('by_amount') == 'on'){
                    $usersQuery->whereHas('factors',function ($query){
                        $query->whereHas('totalFactorItem',function ($query){
                            $query->where('credit', '>', $start_amount)
                            ->where('credit', '<', $end_amount);
                        });
                    });
                }else {
                    $usersQuery->whereHas('factors',function ($query){
                        $query->whereHas('totalFactorItem',function ($query){
                            $query->where('credit', '>', 0);
                        });
                    });
                }
            }
            if (request()->creditType == 'all' ){
                if (request('by_amount') == 'on'){
                    $usersQuery->where('credit' , '>' , $start_amount)
                    ->where('credit' , '<' , $end_amount)
                    ->orWhere('wallet' , '>' ,  $start_amount)
                    ->where('wallet' , '<' ,  $end_amount)
                    ->orWhereHas('factors',function ($query){
                        $query->whereHas('totalFactorItem',function ($query , $start_amount , $end_amount){
                            $query->where('credit', '>', $start_amount)
                            ->where('credit', '<', $end_amount);
                        });
                    });
                }else {
                    $usersQuery->where('credit' , '>' , 0)->orWhere('wallet' , '>' , 0)
                    ->orWhereHas('factors',function ($query){
                        $query->whereHas('totalFactorItem',function ($query){
                            $query->where('credit', '>', 0);
                        });
                    });
                }
            }

            if (isset(request()->excel) && request()->excel == 'on') {
                return (new DebtorUsersExport($usersQuery))->download('debtors.xlsx');
            }

            $users=$usersQuery->paginate(10);

            return  new DebtorCollection($users);
        }
        /**
         * @param User $user
         * @return mixed
         */
        public function show(User $user)
        {
            $factorItems= FactorItem::whereHas('factor',function ($query)use($user){
                $query->whereHas('user', function ($query) use ($user) {
                    $query->where('status', 1)->where('id', $user->id);
                });
            })->with('factors','serviceTariffs','sessions','supervisor.factor')->get();

            $comments =Helper::userHistories($user);
            $items=array();

            foreach ($factorItems as $factorItem){
                $items[]=$this->setItemsFactorItem($factorItem);
            }

            return view('Admin.accounting.show',compact('items','user','comments'));
        }
/**
         * set items to factor_items
         * @param FactorItem $factor_item
         * @return array
         */
        private function setItemsFactorItem( $factor_item)
        {
            $result=array();
            $result[ 'id' ] = $factor_item->id;
            $result[ 'service' ] = $factor_item->service;
            $result[ 'service_item' ] = !is_null( $factor_item->service_item_id ) ? $factor_item->serviceItem : null;
            $result['centers'] = isset($factor_item->service) && $factor_item->service->centers == true ? $factor_item->service->centers : null;
            $result['forms'] = isset($factor_item->service) && isset($factor_item->service->beforeforms) ? $factor_item->service->beforeforms : array();
            $result[ 'complete' ] = $factor_item->complete;
            $result[ 'values' ] = $factor_item->values;
            $result[ 'factor_values' ] = $factor_item->factor_values;
            $result[ 'supervisor_id' ] = $factor_item->supervisor_id;
            $result[ 'contract_forms' ] = ContractForm::all();
            $result[ 'factor_image' ] = $factor_item->factor_image;
            $result[ 'status_factor' ] = $factor_item->status_factor;
            $result['status_user'] = FactorItem::statusFactor($factor_item->status_factor);
            $result['status'] = $factor_item->status;
            $result[ 'confirms' ] = $factor_item->confirms;
            $result[ 'center' ] = $factor_item->center;
            $result[ 'date' ] = $factor_item->date;
            $result[ 'type' ] = $factor_item->type;
            $result[ 'file_number' ] = $factor_item->file_number;
            $result[ 'products' ] = $factor_item->products;
            $result[ 'service_tariffs' ] = $factor_item->serviceTariffs;
            $result[ 'factor_Item_Service_tariffs' ] = $factor_item->factorItemServiceTariffs;
            $result[ 'factorItems' ] = FactorItem::getFatorItemCollection( $factor_item )->get();
            $result['detail']['factor_item']=$factor_item;
            $result['detail']['admin_users']=User::where('center_id', $factor_item->center_id)->where('center_id', '!=' , null)->whereHas('employee', function ($query) {
                $query->where('show_status', 1);
            })->get();
            $result['detail']['histories']=$factor_item->histories;
            $result['detail']['sessions']= $factor_item->sessions;
            $result['detail']['center_service_tariffs']= $factor_item->center ? $factor_item->center->serviceTariffs : [];
            $result['detail']['sum_factor_item_service_tariff_price']= $factor_item->serviceTariffs()->sum('factor_item_service_tariff.price');
            $result = $this->setFinancialItemsFactorItem($result , $factor_item);

            return $result;
        }
        /**
         * set items to factor_items
         * @param FactorItem $factor_item
         * @return array
         */
        private function setFinancialItemsFactorItem($result , $factor_item)
        {
            if($factor_item->type)
            {
                $total_factor_item = $factor_item->totalFactorItem;
                $result[ 'approval_date' ] = verta( $total_factor_item->approval_date )->format( 'Y/m/d' );
                $result[ 'total_price' ] = $total_factor_item->total_price + $total_factor_item->factorItems->sum('discount');
                $result[ 'guarantee_type' ] = $total_factor_item->guarantee_type;
                $result[ 'credit' ] = TotalFactorItem::calculateCredit($factor_item->totalFactorItem);
                $result[ 'total_debt' ] = TotalFactorItem::calculateTotalDebt($factor_item->totalFactorItem);
                $result[ 'discount' ] = $total_factor_item->factorItems->sum('discount');
                $result[ 'prepayment' ]['price'] = $total_factor_item->property()->exists()? $total_factor_item->property->prepayment_price:0;
                $result[ 'prepayment' ]['debt'] = $total_factor_item->property()->exists()? $factor_item->property->prepayment_debt:0;
                $result[ 'prepayment' ]['count'] = $total_factor_item->property()->exists()? $total_factor_item->property->prepayment_count:0;
                $result[ 'prepayment' ]['num'] = $total_factor_item->property()->exists()? $total_factor_item->property->prepayment_num:0;
                $result[ 'percent_fines' ] = $total_factor_item->percent_fines;
                $result['price_difference']['installment'] = $total_factor_item->property()->exists()?$total_factor_item->property->price_difference_installment:null;
                $result['price_difference']['total'] =$total_factor_item->property()->exists()? $total_factor_item->property->price_difference_total:null;
                $result['price_difference']['debt'] =$total_factor_item->property()->exists()? $total_factor_item->property->price_difference_debt:null;
                $result['status_mali'] = TotalFactorItem::statusMali($total_factor_item->status_mali);
                $result[ 'installments_num' ] = $total_factor_item->installments_num;
                $result['installments_price'] = $total_factor_item->totalFactorItemInstallments->first() ? $total_factor_item->totalFactorItemInstallments->first()->price : 0;
                $result['installments_price_pay'] = $total_factor_item->totalFactorItemInstallments->where('status' , 1)->sum('price');
                $result[ 'installments_num_pay' ] = $total_factor_item->totalFactorItemInstallments->where( 'status', 1 )->count();
                $result[ 'credit_amount' ] =$factor_item->property()->exists() && !is_null($total_factor_item->property->price_difference_installment)?  $total_factor_item->property->price_difference_installment : 0;
                $result[ 'start_date' ] = verta( $total_factor_item->approval_date )->format( 'Y/m/d' );
                $result[ 'end_date' ] = isset( $total_factor_item->totalFactorItemInstallments->last()->date ) ? verta( $total_factor_item->totalFactorItemInstallments->last()->date )->format( 'Y/m/d' ) : '';
                $result[ 'payments' ] = $total_factor_item->factors->where('status',1);
                $result[ 'payment' ] = $total_factor_item->factor;
                $result[ 'date_first_pay' ] = $total_factor_item->factors->where( 'status', true)->count() > 0 ? verta( $total_factor_item->factors->where( 'status', true )->first()->created_at )->format( 'Y/m/d' ) : '';
                $result[ 'confirm' ] = $total_factor_item->confirm;
                $result['has_payment'] = $total_factor_item->factors()->where('status' , 1)->exists();
                $result[ 'extra_pay' ] = TotalFactorItem::getExtraPay( $total_factor_item );
                $result[ 'extra_pay_debt' ] = number_format( TotalFactorItem::getDebtExtraPay( $total_factor_item ) );
                $result['loan'] = $total_factor_item->totalFactorItemInstallments->sum('price');
                $result['wage'] = ($result ['total_price'] - $result ['prepayment']['price']) * ($factor_item->factoritemAble->wage_percent/100) / 12 * $result ['installments_num'];
                $result['installments_debt'] = TotalFactorItem::getDebtExtraPay($total_factor_item) + $result['loan'] - $result['installments_num_pay'] * $result['installments_price_pay'];
                $installments = $total_factor_item->totalFactorItemInstallments;
                $installments = new TotalFactorItemInstallmentCollection($installments , $installments);
                $installments = json_encode($installments, true);
                $installments = json_decode($installments, true);
                $result['detail']['installments']=$installments['data'];
                $result[ 'expire_installments' ] =  TotalFactorItem::getExpireInstallments($total_factor_item);
                $result['detail']['factor']=$total_factor_item->factor;
                $result[ 'contract_values' ] = $total_factor_item->contract_values;
                return $result;
            }
            $result[ 'approval_date' ] = verta( $factor_item->approval_date )->format( 'Y/m/d' );
            $result[ 'total_price' ] = $factor_item->total_price + $factor_item->discount;
            $result[ 'guarantee_type' ] = $factor_item->guarantee_type;
            $result[ 'credit' ] = FactorItem::calculateCredit($factor_item->supervisor);
            $result[ 'total_debt' ] = FactorItem::calculateTotalDebt($factor_item->supervisor);
            $result[ 'discount' ] = $factor_item->discount;
            $result[ 'prepayment' ]['price'] = $factor_item->property()->exists()? $factor_item->property->prepayment_price:0;
            $result[ 'prepayment' ]['debt'] = $factor_item->property()->exists()? $factor_item->property->prepayment_debt:0;
            $result[ 'prepayment' ]['count'] = $factor_item->property()->exists()? $factor_item->property->prepayment_count:0;
            $result[ 'prepayment' ]['num'] = $factor_item->property()->exists()? $factor_item->property->prepayment_num:0;
            $result[ 'credit_amount' ] =$factor_item->property()->exists() && !is_null($factor_item->property->price_difference_installment)?  $factor_item->property->price_difference_installment : 0;
            $result[ 'start_date' ] = verta( $factor_item->approval_date )->format( 'Y/m/d' );
            $result[ 'payments' ] = $factor_item->factors->where('status',1);
            $result[ 'payment' ] = $factor_item->factor;
            $result[ 'date_first_pay' ] = $factor_item->factors()->where( 'status', true )->count() > 0 ? verta( $factor_item->factors->first()->created_at )->format( 'Y/m/d' ) : '';
            $result[ 'confirm' ] = $factor_item->confirm;
            $result['has_payment'] = $factor_item->factors()->where('status' , 1)->exists();
            $result['detail']['factor']=$factor_item->factor;

            return $result;
        }
        /**
         * @return void
         */
        public function transfer()
        {
            ini_set ('max_execution_time',  -1);
            TotalFactorItem::chunk(100, function ($factorItems) {
                foreach ($factorItems as $factorItem) {
                if (gettype($factorItem->prepayment['price'])=='string'){
                    $factorItem->update([
                        'prepayment'=>[
                            'price'=>0,
                            'debt'=>0,
                            'count'=>0,
                            'num'=>0,
                        ]
                    ]);
                }
            }
            });
    //        FactorItem::chunk(100, function ($factorItems) {
    //            foreach ($factorItems as $factorItem) {
    //               if (gettype($factorItem->prepayment['price'])=='string'){
    //                   $factorItem->update([
    //                      'prepayment'=>[
    //                          'price'=>0,
    //                          'debt'=>0,
    //                          'count'=>0,
    //                          'num'=>0,
    //                      ]
    //                   ]);
    //               }
    //           }
    //        });
    //
    //        FactorItem::chunk(100, function ($factorItems) {
    //            foreach ($factorItems as $factorItem) {
    //                if ($factorItem->prepayment != null){
    //                    $factorItemAttribute= FactorItemAttributes::where('factor_item_attributable_id',$factorItem->id)->where('factor_item_attributable_type',FactorItem::class)->first();
    //                    $factorItemAttributePrePaymentArray=[
    //                        'prepayment_price'=>isset($factorItem->prepayment['price'])?$factorItem->prepayment['price']:0,
    //                        'prepayment_debt'=>isset($factorItem->prepayment['debt'])?$factorItem->prepayment['debt']:0,
    //                        'prepayment_count'=>isset($factorItem->prepayment['count'])?$factorItem->prepayment['count']:0,
    //                        'prepayment_num'=>isset($factorItem->prepayment['num'])?$factorItem->prepayment['num']:0,
    //                    ];
    //
    //                        if (is_null($factorItemAttribute)){
    //                            $factorItem->property()->create($factorItemAttributePrePaymentArray);
    //                        }else{
    //                            $factorItemAttribute->update($factorItemAttributePrePaymentArray);
    //                        }
    //
    //                }
    //                if ($factorItem->price_difference != null){
    //                    $factorItemAttribute= FactorItemAttributes::where('factor_item_attributable_id',$factorItem->id)->where('factor_item_attributable_type',FactorItem::class)->first();
    //                    $factorItemAttributePriceDifferenceArray=[
    //                        'price_difference_installment'=>isset($factorItem->price_difference['installment'])?$factorItem->price_difference['installment']:0,
    //                        'price_difference_total'=>isset($factorItem->price_difference['total'])?$factorItem->price_difference['total']:0,
    //                        'price_difference_debt'=>isset($factorItem->price_difference['debt'])?$factorItem->price_difference['debt']:0,
    //                    ];
    //
    //                        if (is_null($factorItemAttribute)){
    //                            $factorItem->property()->create($factorItemAttributePriceDifferenceArray);
    //                        }else{
    //                            $factorItemAttribute->update($factorItemAttributePriceDifferenceArray);
    //                        }
    //
    //                }
    //            }
    //        });
            // total factor item transfer

            TotalFactorItem::chunk(100, function ($factorItems) {
                foreach ($factorItems as $factorItem) {
                    if ($factorItem->prepayment != null){
                        $factorItemAttribute= FactorItemAttributes::where('factor_item_attributable_id',$factorItem->id)->where('factor_item_attributable_type',TotalFactorItem::class)->first();
                        $factorItemAttributePrePaymentArray=[
                            'prepayment_price'=>isset($factorItem->prepayment['price'])?$factorItem->prepayment['price']:0,
                            'prepayment_debt'=>isset($factorItem->prepayment['debt'])?$factorItem->prepayment['debt']:0,
                            'prepayment_count'=>isset($factorItem->prepayment['count'])?$factorItem->prepayment['count']:0,
                            'prepayment_num'=>isset($factorItem->prepayment['num'])?$factorItem->prepayment['num']:0,
                        ];

                            if (is_null($factorItemAttribute)){
                                $factorItem->property()->create($factorItemAttributePrePaymentArray);
                            }else{
                                $factorItemAttribute->update($factorItemAttributePrePaymentArray);
                            }

                    }
                    if ($factorItem->price_difference != null){
                        $factorItemAttribute= FactorItemAttributes::where('factor_item_attributable_id',$factorItem->id)->where('factor_item_attributable_type',FactorItem::class)->first();
                        $factorItemAttributePriceDifferenceArray=[
                            'price_difference_installment'=>isset($factorItem->price_difference['installment'])?$factorItem->price_difference['installment']:0,
                            'price_difference_total'=>isset($factorItem->price_difference['total'])?$factorItem->price_difference['total']:0,
                            'price_difference_debt'=>isset($factorItem->price_difference['debt'])?$factorItem->price_difference['debt']:0,
                        ];

                            if (is_null($factorItemAttribute)){
                                $factorItem->property()->create($factorItemAttributePriceDifferenceArray);
                            }else{
                                $factorItemAttribute->update($factorItemAttributePriceDifferenceArray);
                            }
                    }
                }
            });
        }
    }
