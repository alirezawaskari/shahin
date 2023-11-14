<?php

namespace App\Http\Controllers;

use App\Helpers\Helper;
use App\Models\ContractForm;
use App\Models\Disease;
use App\Models\Factor;
use App\Models\FactorItem;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CheckoutController extends Controller {
    /**
    * @param Factor $factor
    * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
    */
    public function index( Factor $factor ) {
        $user = auth()->user();
        $diseases=Disease::all();
        $items = $factor->factorItems->map( function ( $item ) {
            $temp = [];

            $temp[ 'id' ] = $item->id;
            $temp[ 'service' ] = $item->factoritemable;
            $temp[ 'service_item' ] = !is_null( $item->service_item_id ) ? $item->serviceItem : null;
            $temp[ 'centers' ] = FactorItem::getSelectableCenters($item);
            $temp[ 'forms' ] = count( $item->factoritemable->beforeforms ) != 0 ? $item->factoritemable->beforeforms : null;
            $temp[ 'complete' ] = $item->complete;
            $temp[ 'type' ] = $item->type;
            $temp[ 'factor_values' ] = $item->factor_values;
            $temp[ 'contract_forms' ] = ContractForm::where( 'level', 'basic_documents' )->get();
            $temp[ 'contract_values' ] = $item->totalFactorItem ? $item->totalFactorItem->contract_values : '';
            $temp[ 'status_factor' ] = $item->status_factor;
            $temp[ 'total_price' ] = $item->total_price;
            $temp[ 'prepayment' ] = $item->prepayment;
            $temp[ 'guarantee_type' ] = $item->totalFactorItem()->exists()? $item->totalFactorItem->guarantee_type:'check';
            $temp[ 'installments' ] = $item->installments;
            $temp[ 'installments_num' ] = $item->installments_num;
            $temp[ 'products' ] = $item->products;
            $temp[ 'center_id' ] = $item->center_id;
            $temp[ 'is_changeable' ] = self::checkIsChangeable( $item );

            return $temp;
        }
    );
    $check = $factor->factorItems()->where( 'complete', false )->exists();

    return view( 'site.checkout', compact( 'factor', 'items', 'check', 'user' ,'diseases') );
}

/**
* @param Request $request
* @return \Illuminate\Http\RedirectResponse
* @throws \Exception
*/

public function store( Request $request ) {
    $validator = Validator::make( $request->items, [
        '*.id' => array( 'required', Rule::exists( 'factor_items', 'id' ) ),
        '*.center' =>  array( Rule::exists( 'centers', 'id' ) ),
        '*.type'    => array( Rule::in( 0, 1 ) ),
        '*.guarantee_type'=> array( 'nullable', Rule::in( 'check', 'certificate', 'promissorynote' ) ),
    ] );
    if ( $validator->fails() ) {
        throw new \Exception( '[request value is invalid] '.implode( '_', $validator->errors()->all() ) );
    }
    foreach ( $request->items as $item ) {
        $factor_item = FactorItem::find( $item[ 'id' ] );
        if ( !self::checkIsChangeable( $factor_item ) ) {
            return redirect()->back();
        }
        if ( $factor_item->factor->user_id != auth()->user()->id && $factor_item->factor->user_id != $factor_item->supervisor_id ) {
            abort( 403 );
        }

        isset( $item[ 'type' ] ) ? FactorItem::changeType( $factor_item, $item[ 'type' ] ) : '';
        if ( isset( $item[ 'type' ] ) && $item[ 'type' ] ) {
            $factor_item = FactorItem::find( $factor_item->id );
            $factor_item->totalFactorItem->update( [
                'guarantee_type' => $item[ 'guarantee_type' ] ,
            ] );
        }

        if ( $factor_item->factoritemable->center ) {
            $factor_item->update( [
                'center_id' =>  $item[ 'center' ],
                'type' => $item[ 'type' ] ,
            ] );
            $factor_item->factor->update( [ 'representation_id' => $factor_item->center->representation_id ] );
            //Send SMS center
            //event( new smsService( $factor_item->factor->user, $factor_item->center->title . ':' . $factor_item->center->address, $factor_item->date ) );
        }

        $this->formData( $item );

        $factor_item->update( [
            'status_factor' => isset( $item[ 'type' ] ) && $item[ 'type' ] ? 4 : 14,
            'complete' => true,
        ] );
    }
    return redirect()->back();
}

/**
* @param Request $request
* @return \Illuminate\Http\RedirectResponse
* @throws \Exception
*/

public function upload_document( Request $request ) {

    $this->validate( $request, [
        'item.id' => array( 'required', Rule::exists( 'factor_items', 'id' ) ),
        'item.imgauthentication' => array( 'nullable', 'mimes:jpg,jpeg,bmp,png' ),
        'item.imgturnover' => array( 'nullable', 'mimes:jpg,jpeg,bmp,png' ),
        'item.imgsamplecheck' => array( 'nullable', 'mimes:jpg,jpeg,bmp,png' ),
        'item.imgczech' => array( 'nullable', 'mimes:jpg,jpeg,bmp,png' ),
        'item.imgemploymentorder' => array( 'nullable', 'mimes:jpg,jpeg,bmp,png' ),
        'imgmeli' => array( 'nullable', 'mimes:jpg,jpeg,bmp,png' ),
        'imgshenasname' => array( 'nullable', 'mimes:jpg,jpeg,bmp,png' ),
    ] );

    $item = $request->item;
    $factor_item = FactorItem::find( $item[ 'id' ] );
    if ( !self::checkIsChangeable( $factor_item ) ) {
        return redirect()->back();
    }
    if (
        $factor_item->factor->user_id != auth()->user()->id
        && $factor_item->factor->user_id != $factor_item->supervisor_id
        && $request->user_id != auth()->user()->id
        && $request->user_id != $factor_item->supervisor_id ) {
            abort( 403 );
        }

        $user = User::find( $request->user_id );
        $imgmeli = $request->file( 'imgmeli' );
        $imgshenasname = $request->file( 'imgshenasname' );
        if ( $imgmeli ) {
            $urlimgmeli = Helper::uploadImage( $imgmeli, 'document' );
            $user->update( [ 'imgmeli' => $urlimgmeli ] );
        }
        if ( $imgshenasname ) {
            $urlimgshenasname = Helper::uploadImage( $imgshenasname, 'document' );
            $user->update( [ 'imgshenasname' => $urlimgshenasname ] );
        }

        $this->formData( $request->item );

        $factor_item->update( [ 'status_factor' => 14 ] );

        return redirect()->back()->with( [
            'send' => true
        ] );
    }

    /**
    * @param $item
    * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Foundation\Application|\Illuminate\Http\Response
    */

    public function formData( $item ) {
        $factor_item = FactorItem::find( $item[ 'id' ] );
        $contract_values = $factor_item->totalFactorItem && $factor_item->totalFactorItem->contract_values ? $factor_item->totalFactorItem->contract_values : [];
        $values = $factor_item->factor_values ? $factor_item->factor_values : [];
        foreach ( $factor_item->factoritemable->beforeforms->where( 'level', 'site' ) as $key => $form ) {
            if ( $form->type == 'file' ) {
                if ( isset( $item[ $form->name ] ) && !is_null( $item[ $form->name ] ) ) {
                    $img = $item[ $form->name ];
                    if ( $img && file_exists( $img ) ) {
                        $url = Helper::uploadImage( $img, 'document' );
                        if ( isset( $values[ $form->name ] ) ) {
                            $values[ $form->name ] = $url;
                        }
                        $values = Arr::add( $values, $form->name, $url );
                    }
                }
            } elseif ( $form->type == 'multi_file' ) {
                if ( isset( $item[ $form->name ] ) ) {
                    $images = isset( $values[ $form->name ] ) ? $values[ $form->name ] : [];
                    $files = $item[ 'url' . $form->name ];
                    foreach ( $files as $file ) {
                        if ( $file ) {
                            if ( isset( $images ) ) {
                                $url = Helper::moveImage( $file, 'document' );
                                $images[] = $url;
                            }
                        }
                    }
                    if ( isset( $values[ $form->name ] ) ) {
                        $values[ $form->name ] = $images;
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
            $factor_item->update( [
                'factor_values' => !is_null( $values ) ? $values : null,
            ] );
        }
        foreach ( ContractForm::where( 'level', 'basic_documents' )->get() as $key => $contract_form ) {
            if ( $contract_form->type == 'file' ) {
                if ( isset( $item[ $contract_form->name ] ) && !is_null( $item[ $contract_form->name ] ) ) {
                    $img = $item[ $contract_form->name ];
                    if ( $img && file_exists( $img ) ) {
                        $url = Helper::uploadImage( $img, 'document' );
                        if ( isset( $contract_values[ $contract_form->name ] ) ) {
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
            if ( $factor_item->totalFactorItem ) {
                $factor_item->totalFactorItem->update( [
                    'contract_values' => !is_null( $contract_values ) ? $contract_values : null,
                ] );
            }
        }
        return response( 'true' );
    }

    /**
    * @param mixed $item //factorItem
    * @return bool
    */
    private function checkIsChangeable( $item ) {
        $temp = in_array( $item->status_factor, array( 0, 4, 14, 3, 2, 9 ) );
        //check totalFactorItem confirmation
        if ( $item->totalFactorItem && $item->totalFactorItem->confirm ) {
            $temp = false;
        }

        return $temp;
    }
}
