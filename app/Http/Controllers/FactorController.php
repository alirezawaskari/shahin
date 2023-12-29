<?php

namespace App\Http\Controllers;

use App\Helpers\Helper;
use App\Models\Callcenter;
use App\Models\Callcenterstatus;
use App\Models\Coupon;
use App\Models\Factor;
use App\Models\FinancialPlansType;
use App\Models\Service;
use App\Models\User;
use Carbon\Carbon;
use Doctrine\DBAL\Exception\DatabaseObjectExistsException;
use Illuminate\Http\Request;

class FactorController extends Controller {
    /**
    * @param Request $request
    * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
    * @throws \Illuminate\Validation\ValidationException
    */

    public function create( Request $request ) {
        $cart = session()->get( 'cart' );

        if ( $cart )
        {
            $representation = $cart[ 'representation' ] == 0 ? null : $cart[ 'representation' ];
            $admin_user = Helper::getCallcenter(auth()->user()->province_id , $representation);
            $admin_user_representation = User::find($admin_user)->admin_representation_id;
            $status = auth()->user()->organization_id && auth()->user()->credit >= $cart[ 'total' ][ 'price' ] + $cart[ 'total' ][ 'price_tax' ] ? true : false;
            $factor_items = Factor::setFactorItemsInArray($cart[ 'items' ]);
            $factor = Factor::checkExistSimilarFactor($factor_items , auth()->user());
            if(!$factor)
            {
                $factor = auth()->user()->factors()->create( [
                    'status' => $cart[ 'total' ][ 'price' ] == 0 ? true : $status,
                    'confirm' => false,
                    'price' => $cart[ 'total' ][ 'price' ] + $cart[ 'total' ][ 'price_tax' ],
                    'tax' => $cart[ 'total' ][ 'price_tax' ],
                    'task_id'=>isset( $cart[ 'task_id' ] ) ? $cart[ 'task_id' ] : null,
                    'count' => $cart[ 'total' ][ 'count' ],
                    'representation_id' => $admin_user_representation,
                    'callcenter_id' => $cart[ 'callcenter' ] == 0 ? null : $cart[ 'callcenter' ],
                    'type' => auth()->user()->organization_id ? 'credit' : 'online',
                    'expired_at' => now()->addDay( 3 ),
                ] );
                if ( !isset( $cart[ 'task_id' ] ) ) {
                    self::createTask( $factor );
                }
                $factor->price == 0 ? $factor->task->update(['status' => 10]) : '';
                foreach ( $cart[ 'items' ] as $item ) {
                    auth()->user()->update( [ 'service_id' => $item['service_id']] );
                    $financial_plans_type = FinancialPlansType::findOrFail($item[ 'financial_plans_type_id' ]);
                    $factor_item = $factor->factorItems()->create( [
                        'supervisor_id' => $factor->user_id,
                        'factoritemable_id' => $item[ 'service_id' ],
                        'factoritemable_type' => Service::class,
                        'financial_plans_type_id' => $item[ 'financial_plans_type_id' ],
                        'count' => 1,
                        'type' => $financial_plans_type ? $financial_plans_type::getPaymentType($financial_plans_type->payment_type) : 0,
                        'price' => $item[ 'price' ],
                    ] );
                    $factor_item->update( [ 'supervisor_id' => $factor_item->id ] );

                    if($factor_item->type)
                    {
                        //if the factorItem is credit, create the factor totalFactorItem
                        $total_factor_item = $factor->totalFactorItem()->create([
                            'factor_id' => $factor->id,
                            'total_factor_item_status_id' => 0,
                            'status_mali' => 0,
                        ]);
                        $factor_item->update(['total_factor_item_id' => $total_factor_item->id]);
                    }
                }
            }

            //event( new fastserviceCallcenter( auth()->user(), $factor, auth()->user()->family, 'online' ) );
            //$factor->status ? '' : event( new sendlinkFactor( auth()->user(), $factor, auth()->user()->family, 'online' ) );

            session()->forget( 'cart' );

            return redirect( route( 'factor', [$factor->resnumber ] ) );
        }
        return redirect()->back();
    }

    /**
    * @param Factor $factor
    * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
    */

    public function index( Factor $factor ) {
        $debt_price = self::setDebtPriceByFactorTitle($factor);
        $factor->load( 'user', 'representation' );
        $representation=null;
            if($factor->representation)
            {
                $representation = $factor->representation;
                session()->put('representation', $representation);
//                return view( 'site.representation.factor', compact( 'factor' , 'representation' , 'debt_price') );
            }
            return view( 'site.factor', compact( 'factor' , 'debt_price','representation') );
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
    * @param $factor
    * @return float|int
    */
    private static function discount( $factor ) {
        $amount = $factor->price;
        if ( !is_null( $factor->coupon_id ) ) {
            $amount = $amount - ( $amount * $factor->coupon->discount / 100 );
        }
        return $amount;
    }

    /**
     * @param $factor
     */
    private function createTask($factor ) {
        $user = $factor->user;
        $callcenter_id = Helper::getCallcenter($user->province_id , $factor->representation_id);
        $task = Callcenter::create( [
            'reg_id' => $user->id,
            'user_id' => $callcenter_id,
            'type' => 0,
            'representation_id' => $factor->representation_id,
            'status' => '0'
        ] );
        Callcenterstatus::create( [
            'user_id' => $callcenter_id,
        ] );
        $factor->update( [ 'task_id' => $task->id ] );
    }
    /**
     * @param Factor $factor
     * @return string
     */
    private function setDebtPriceByFactorTitle(Factor $factor)
    {
        $debt_price = 0;
        switch ($factor->title) {
            case 'factor':
                $debt_price = $factor->factorItem ? $factor->factorItem->property->prepayment_debt : 0;
                break;
            case 'prepayment':
                $debt_price = $factor->totalFactorItem->property->prepayment_debt;
                break;
            case 'price_difference':
                $debt_price = $factor->totalFactorItem->property->price_difference_debt;
                break;
            case 'installment':
                $debt_price = $factor->totalFactorItem->totalFactorItemInstallments->where('status', 0)->sum('price');
                break;
            default:
                $debt_price = 0;
        }
        return number_format($debt_price - $factor->price > 0 ? $debt_price - $factor->price : 0);
    }

}
