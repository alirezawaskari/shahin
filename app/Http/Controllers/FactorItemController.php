<?php

namespace App\Http\Controllers;

use App\Classes\External\IranSms;
use App\Classes\External\SendSms;
use App\Helpers\Helper;
use App\Models\AuthCode;
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

class FactorItemController extends Controller {
    /**
    * @param Request $request
    * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
    * @throws \Psr\Container\ContainerExceptionInterface
    * @throws \Psr\Container\NotFoundExceptionInterface
    */

    public function create( Request $request ) {
        $cart = session()->get( 'cart' );
        $factor = auth()->user()->factors()->create( [
            'status' => false,
            'confirm' => false,
            'price' => $cart[ 'total' ][ 'price' ] + $cart[ 'total' ][ 'price' ] * 9 / 100,
            'tax' => $cart[ 'total' ][ 'price' ] * 9 / 100,
            'count' => $cart[ 'total' ][ 'count' ],
            'representation_id' => $cart[ 'representation' ] == 0 ? null : $cart[ 'representation' ],
            'expired_at' => now()->addDay( 3 ),
        ] );
        foreach ( $cart[ 'items' ] as $item ) {
            $factor->factor_items()->create( [
                'factoritemable_id' => $item[ 'type' ] == 'service' ? $item[ 'id' ] : ServiceItem::find( $item[ 'id' ] )->service->id,
                'service_item_id' => $item[ 'type' ] == 'service' ? null : $item[ 'id' ],
                'count' => 1,
                'price' => $item[ 'price' ],
            ] );
        }
        session()->forget( 'cart' );
        return redirect( route( 'factor', [ $factor->resnumber ] ) );
    }

    /**
    * @param FactorItem $factorItem
    * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
    */

    public function index( FactorItem $factorItem ) {
        if ( !$factorItem->status ) {
            $factorItem->load( 'factor' );
            return view( 'site.factorItem', compact( 'factorItem' ) );
        }
        return redirect( route( 'profile.factor' ) );
    }

    /**
    * @param FactorItem $factorItem
    * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
    */

    public function show( FactorItem $factorItem ) {
        $expire_installments = $factorItem->factorItemInstallments->where( 'status', 0 )->where( 'date', '<', Carbon::now() );
        $paid_installments = $factorItem->factorItemInstallments->where( 'status', 1 );
        $factor = $factorItem->factor;
        $user = $factorItem->factor->user;
        $type = $factorItem->factor->factorable_type;
        $comments = $factorItem->factor->comments;
        $totalFactorItem = $factorItem->supervisor->totalFactorItem ?? '';
        $item = [];
        $item[ 'id' ] = $factorItem->id;
        $item[ 'service' ] = $factorItem->factoritemable;
        $item[ 'service_item' ] = !is_null( $factorItem->service_item_id ) ? $factorItem->serviceItem : null;
        $item[ 'centers' ] = $factorItem->factoritemable->centers == true ? $factorItem->factoritemable->centers : null;
        $item[ 'forms' ] = count( $factorItem->factoritemable->beforeforms ) != 0 ? $factorItem->factoritemable->beforeforms : null;
        $item[ 'complete' ] = $factorItem->complete;
        $item[ 'values' ] = $factorItem->values;
        $item[ 'factor_values' ] = $factorItem->factor_values;
        $item[ 'supervisor_id' ] = $factorItem->supervisor_id;
        $item[ 'factor_image' ] = $factorItem->factor_image;
        $item[ 'status_factor' ] = $factorItem->status_factor;
        $item[ 'status_user' ] = FactorItem::statusFactor( $factorItem->status_factor );
        $item[ 'confirms' ] = $factorItem->confirms;
        $item[ 'center' ] = $factorItem->center;
        $item[ 'date' ] = $factorItem->date;
        $item[ 'type' ] = $factorItem->type;
        $item[ 'file_number' ] = $factorItem->file_number;
        $item[ 'discount' ] = $factorItem->discount;
        $item[ 'complete' ] = $factorItem->status_factor == 6 || $factorItem->status_factor == 10 ? true : false;
        $item[ 'factorItems' ] = FactorItem::getFatorItemCollection( $factorItem )->get();
        $item[ 'service_tariffs' ] = $factorItem->serviceTariffs;
        $item[ 'confirm' ] = $totalFactorItem->confirm??false;
        $item[ 'total_price' ] = $factorItem->supervisor->total_price + $factorItem->supervisor->discount;
        $item[ 'prepayment' ][ 'price' ] = $factorItem->supervisor->property ? $factorItem->supervisor->property->prepayment_price : 0;
        $item[ 'payments' ] = $factorItem->factors;
        $item[ 'date_first_pay' ] = isset( $factorItem->factors->where( 'title', '!=', 'register' )->where( 'status', true )->first()->created_at ) ? verta( $factorItem->factors->where( 'title', '!=', 'register' )->first()->created_at )->format( 'Y/m/d' ) : '';
        $item[ 'start_date' ] = verta( $factorItem->approval_date )->format( 'Y/m/d' );
        //if factorItem type is installment
        if ( $totalFactorItem ) {
            $item['is_pay_installment_by_check'] = TotalFactorItem::payInstallmentByCheck($totalFactorItem);
            $item['count_installment_checks'] = TotalFactorItem::getCountInstallmentChecks($totalFactorItem);
            $item[ 'guarantee_type' ] = $totalFactorItem->guarantee_type;
            $item[ 'contract_values' ] = $totalFactorItem->contract_values;
            $item[ 'status_mali' ] = $factorItem->status_mali;
            $item[ 'start_date' ] = verta( $totalFactorItem->approval_date )->format( 'Y/m/d' );
            $item[ 'status_mali_text' ] = FactorItem::statusMali( $totalFactorItem->status_mali );
            $item[ 'approval_date' ] = verta( $factorItem->approval_date )->format( 'Y/m/d' );
            $item[ 'total_price' ] = $totalFactorItem->total_price + $totalFactorItem->factorItems->sum( 'discount' );
            $item[ 'prepayment' ][ 'price' ] = $totalFactorItem->property()->exists() ? $totalFactorItem->property->prepayment_price : 0;
            $item[ 'installments_num' ] = $totalFactorItem->installments_num;
            $item['installments_price'] = $totalFactorItem->totalFactorItemInstallments->first() ? $totalFactorItem->totalFactorItemInstallments->first()->price : 0;
            $item['wage'] = TotalFactorItem::getWage($totalFactorItem);
            $item[ 'loan' ] = $totalFactorItem->totalFactorItemInstallments->sum( 'price' );
            $item[ 'credit' ] = $totalFactorItem->credit;
            $item[ 'extra_pay' ] = TotalFactorItem::getExtraPay( $totalFactorItem );
            $item[ 'date_first_pay' ] = isset( $totalFactorItem->factors->where( 'title', '!=', 'register' )->where( 'status', true )->first()->created_at ) ? verta( $totalFactorItem->factors->where( 'title', '!=', 'register' )->first()->created_at )->format( 'Y/m/d' ) : '';
            $item[ 'price_difference' ] = $totalFactorItem->property ? $totalFactorItem->property->price_difference : 0;
            $item[ 'payments' ] = $totalFactorItem->factors;
            $item['percent_fines'] = $totalFactorItem->percent_fines;
            $item['end_date'] = isset($totalFactorItem->totalFactorItemInstallments->last()->date) ? verta($totalFactorItem->totalFactorItemInstallments->sortBy('date')->last()->date)->format('Y/m/d') : '';
        }
        $resnumber = $totalFactorItem ? TotalFactorItem::setFatorItemCollectionResnumber( $totalFactorItem ) : '';
        $contract_number = $totalFactorItem ? TotalFactorItem::setAgentContractNumber( $totalFactorItem ) : '';
        $agents_contract_file = $totalFactorItem ? TotalFactorItem::setAgentContractFile( $totalFactorItem ) : '';
        $admin_users = User::where( 'center_id', $factorItem->center_id )->where( 'center_id', '!=', null )->get();
        $doctors = [];
        foreach ( $admin_users as $u ) {
            if ( $u->hasRole( 'doctor' ) ) {
                $doctors[] = $u;
            }
        }
        $checks = $totalFactorItem ? $totalFactorItem->checks : null;
        $sessions = $factorItem->sessions;
        return view( 'site.profile.layouts.factoritem.show', compact( 'checks' , 'agents_contract_file', 'resnumber', 'contract_number', 'totalFactorItem', 'expire_installments', 'paid_installments', 'sessions', 'doctors', 'factor', 'factorItem', 'user', 'type', 'comments', 'item' ) );
    }

    /**
    * @param Request $request
    * @return \Illuminate\Http\RedirectResponse
    * @throws \Illuminate\Validation\ValidationException
    */

    public function coupon( Request $request ) {
        $this->validate( $request, [
            'code' => 'required|exists:coupons,code',
            'factor_id' => 'required',
        ] );
        $factor = Factor::findOrFail( $request->input( 'factor_id' ) );
        $date = Carbon::now()->toDateString();
        $coupon = Coupon::whereStatus( true )->where( 'start_at', '<=', $date )->where( 'end_at', '>=', $date )->first();

        if ( $coupon ) {
            if ( $coupon->capacity > $coupon->reserved ) {
                $factor->update( [ 'coupon_id' => $coupon->id ] );
                $coupon->reserved++;
                $coupon->save();
            } else {
                return redirect()->back()->withErrors( [ 'msg' => 'مهلت کد تخفیف به پایان رسیده است!' ] );
            }
        } else {
            return redirect()->back()->withErrors( [ 'msg' => 'کد تخفیف مورد نظر یافت نشد!' ] );
        }
        return redirect()->back();
    }

    /**
    * @param Request $request
    * @return \Illuminate\Http\RedirectResponse
    */

    public function requestContractSettlement( Request $request ) {
        $total_factor_item = TotalFactorItem::find( $request->item[ 'id' ] );
        if ( !in_array( $total_factor_item->status_mali, [ 7, 6, 8, 9 ] ) ) {
            $applicant_id = auth()->user()->id;
            $total_factor_item_contract_settlement = $total_factor_item->totalFactorItemContractSettlement()->create( [
                'applicant_id' => $applicant_id,
                'description_request_contract_settlement' => $request->description_request_contract_settlement,
            ] );
            if ( $request->urlrequest_contract_settlement ) {
                Helper::deleteImage( $total_factor_item_contract_settlement->img_request_contract_settlement );
                $total_factor_item_contract_settlement->update( [ 'img_request_contract_settlement' => Helper::moveImage( $request->urlrequest_contract_settlement, 'document' ) ] );
            }
            $total_factor_item->update( [ 'status_mali' => 7 ] );
            return redirect()->back()->with( [
                'successByMessage' => 'درخواست تسویه حساب با موفقیت ثبت شد.'
            ] );
        }
        return redirect()->back()->with( [
            'failByMessage' => 'درخواست تسویه حساب قبلا ثبت شده است.'
        ] );
    }

    /**
    * @param $item
    * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Foundation\Application|\Illuminate\Http\Response
    */

    public function formData( $item ) {
        $factor_item = FactorItem::find( $item[ 'id' ] );
        $values = $factor_item->factor_values ? $factor_item->factor_values : [];
        $contract_values = $factor_item->contract_values ? $factor_item->contract_values : [];
        foreach ( $factor_item->factoritemable->beforeforms as $key => $form ) {
            if ( $form->data_save_level == 'user' ) {
                if ( isset( $item[ $form->name ] ) ) {
                    User::where( 'id', $item[ 'user_id' ] )->update( [ $form->name => $item[ $form->name ] ] );
                }
                if ( $form->type == 'file' ) {
                    if ( isset( $item[ $form->name ] ) ) {
                        $image = $item[ $form->name ];
                        if ( $image ) {
                            $urlimage = Helper::uploadImage( $image, 'document' );
                            User::where( 'id', $item[ 'user_id' ] )->update( [ $form->name => $urlimage ] );
                        }
                    }
                }
            }
            if ( $form->data_save_level == 'factor_item_factor_values' ) {
                if ( $form->type == 'file' ) {
                    if ( isset( $item[ $form->name ] ) ) {
                        $image = $item[ $form->name ];
                        if ( $image ) {
                            $urlimage = Helper::uploadImage( $image, 'document' );
                            $values[ $form->name ] = $urlimage;
                            $values = Arr::add( $values, $form->name, $item[ $form->name ] );
                        }
                    }
                } elseif ( $form->type == 'multi_file' ) {
                    if ( isset( $item[ $form->name ] ) ) {
                        $images = isset( $values[ $form->name ] ) ? $values[ $form->name ] : [];
                        $files = $item[ $form->name ];
                        foreach ( $files as $file ) {
                            if ( $file ) {
                                $url = Helper::uploadImage( $file, 'document' );
                                if ( isset( $images ) ) {
                                    $images[] = $url;
                                    $values[ $form->name ] = $images;
                                }
                            }
                        }
                        $values = Arr::add( $values, $form->name, $images );
                    }
                } else {
                    if ( isset( $item[ $form->name ] ) ) {
                        if ( isset( $values[ $form->name ] ) ) {
                            $values[ $form->name ] = $item[ $form->name ];
                        }
                        $values = Arr::add( $values, $form->name, $item[ $form->name ] );
                    }
                }
            }
            $factor_item->update( [
                'factor_values' => !is_null( $values ) ? $values : null,
            ] );
        }
        foreach ( ContractForm::all() as $key => $contract_form ) {
            if ( $contract_form->type == 'other_document_image' ) {
                if ( isset( $item[ $contract_form->name ] ) ) {
                    $images = isset( $contract_values[ $contract_form->name ] ) ? $contract_values[ $contract_form->name ] : [];
                    $files = $item[ $contract_form->name ];
                    foreach ( $files as $file ) {
                        if ( $file ) {
                            $url = Helper::uploadImage( $file, 'document' );
                            if ( isset( $images ) ) {
                                $images[] = $url;
                                $contract_values[ $contract_form->name ] = $images;
                            }
                        }
                    }
                    $contract_values = Arr::add( $contract_values, $contract_form->name, $images );
                }
            }
            if ( $contract_form->type == 'file' ) {
                if ( isset( $item[ $contract_form->name ] ) ) {
                    $img = $item[ $contract_form->name ];
                    if ( $img ) {
                        $url = $item[ 'url'.$contract_form->name ];
                        if ( isset( $contract_values[ $contract_form->name ] ) ) {
                            $contract_values[ $contract_form->name ] != $url ? Helper::deleteImage( $contract_values[ $contract_form->name ] ) : '';
                            $contract_values[ $contract_form->name ] = $url;
                        }
                        $contract_values = Arr::add( $contract_values, $contract_form->name, $url );
                    }
                }
            }
            if ( $contract_form->type == 'text' || $contract_form->type == 'select' ) {
                if ( isset( $item[ $contract_form->name ] ) ) {
                    if ( isset( $contract_values[ $contract_form->name ] ) ) {
                        $contract_values[ $contract_form->name ] = $item[ $contract_form->name ];
                    }
                    $contract_values = Arr::add( $contract_values, $contract_form->name, $item[ $contract_form->name ] );
                }
            }
            $factor_item->update( [
                'contract_values' => !is_null( $contract_values ) ? $contract_values : null,
            ] );
        }
        return response( 'true' );
    }

    /**
    * @param Request $request
    * @return string
    */

    public function uploadSingleImage( Request $request ) {
        return Helper::uploadImage( $request->file, 'document' );
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response|\Illuminate\Contracts\Routing\ResponseFactory
     */
    public function sendConfirmContractCode(Request $request)
    {
        if($request->confirm_contract_check == true)
        {
            $user_phone_number = auth()->user()->phone;
            $verification_code = rand( 123456, 999999 );
            $auth_code = AuthCode::where('phone' , auth()->user()->phone)->first();
            if(!is_null($auth_code))
            {
                $expirationTime = time() - $auth_code->expiration_time;
                $remaining_time = ($auth_code->time_code_again * 60) - $expirationTime;
                if ($remaining_time > 0) {
                    $date['remaining_time_second'] = $remaining_time;
                    $message = '  کاربر گرامی لطفا بعد از  ' . $remaining_time . '  ثانیه تلاش کنید.';
                    return response( [ 'status' => 'error', 'message' => $message ] );
                }
                $auth_code->update([
                    'code' => $verification_code,
                    'expiration_time' => time(),
                ]);
            }
            else
            {
                AuthCode::create([
                    'phone' => $user_phone_number,
                    'code' => $verification_code,
                    'time_code_again' => 2,
                    'expiration_time' => time(),
                ]);
            }
            SendSms::sendSingleSms('sendConfirmContractCode',$user_phone_number,['param1'=>$verification_code ]);
            return response( [ 'status' => 'success', 'message' => 'کد با موفقیت ارسال گردید.' ] );
        }
        return response( [ 'status' => 'error', 'message' => 'لطفا بر روی قسمت تایید میکنم کلیک کنید. ' ] );
    }
    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response|\Illuminate\Contracts\Routing\ResponseFactory
     */
    public function verificationConfirmContractCode( Request $request )
    {
        $has_auth_code = AuthCode::where('phone' , auth()->user()->phone)->where('code' , $request->verification_code)->exists();
        if($has_auth_code)
        {
            if($request->total_factor_item)
            {
                $totalFactorItem = TotalFactorItem::find($request->total_factor_item);
                $factor_items = $totalFactorItem->factorItems;
                $totalFactorItem->update([
                    'total_factor_item_status_id' => 8
                ]);
                foreach ($factor_items as $factor_item) {
                    $fator_item_collections = FactorItem::getFatorItemCollection($factor_item);

                    $fator_item_collections->update([
                        'status_factor' => 8
                    ]);
                }
            }
            else
            {
                $factorItem = FactorItem::find($request->factor_item);
                $factorItem->update([
                    'status_factor' => 8
                ]);
            }

            return response( [ 'status' => 'success', 'message' => 'قرارداد با موفقیت امضا شد.' ] );
        }
        return response( [ 'status' => 'error', 'message' => 'کد وارد شده با کد ارسالی مطابقت ندارد.' ] );
    }
    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response|\Illuminate\Contracts\Routing\ResponseFactory
     */
    public function verificationConfirmContractCodeEmployee( Request $request )
    {
        $has_auth_code = AuthCode::where('phone' , auth()->user()->phone)->where('code' , $request->verification_code)->exists();
        if($has_auth_code)
        {
            auth()->user()->employee()->update(['confirm' => true,'status' => 1]);
            return response( [ 'status' => 'success', 'message' => 'قرارداد با موفقیت امضا شد.' ] );
        }
        return response( [ 'status' => 'error', 'message' => 'کد وارد شده با کد ارسالی مطابقت ندارد.' ] );
    }
}

