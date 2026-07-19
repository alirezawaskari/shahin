<?php

namespace App\Http\Controllers;

use App\Events\referralCenterNotification;
use App\Helpers\Helper;
use App\Helpers\MediaLibraryHelper;
use App\Models\ContractForm;
use App\Models\Disease;
use App\Models\Factor;
use App\Models\FactorItem;
use App\Models\Session;
use App\Models\TotalFactorItem;
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
        $diseases = Disease::all();
        $items = $factor->factorItems->map( function ( $item ) {
            $temp = [];

            $temp[ 'id' ] = $item->id;
            // the models themselves, so the view can resolve uploaded documents through
            // Spatie ($item['factor_item']->documentUrls(<form name>)) rather than reading
            // paths out of the factor_values/contract_values JSON.
            $temp[ 'factor_item' ] = $item;
            $temp[ 'total_factor_item' ] = $item->totalFactorItem;
            $temp[ 'service' ] = $item->factoritemable;
            $temp[ 'service_item' ] = !is_null( $item->service_item_id ) ? $item->serviceItem : null;
            $temp[ 'centers' ] = FactorItem::getSelectableCenters( $item );
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
            $temp[ 'resnumber' ] = $item->resnumber;
            $temp[ 'is_changeable' ] = self::checkIsChangeable( $item );

            return $temp;
        }
    );
    $check = $factor->factorItems()->where( 'complete', false )->exists();

    return view( 'site.checkout', compact( 'factor', 'items', 'check', 'user', 'diseases' ) );
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
            return redirect()->back()->with( [
                'failByMessage' => 'امکان بارگذاری مدارک برای پرونده شما وجود ندارد.'
            ] );
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

        if ( isset( $item[ 'center' ] ) && $factor_item->factoritemable->center ) {
            $request['description'] = ' - درخواست مشاوره - انتخاب شعبه توسط کاربر '.$request->description;
            $request['center_id'] = $item[ 'center' ];
            if($factor_item->center_id != $request['center_id'])
            {
                FactorItem::referralCenter($factor_item , $request);
            }
        }
        $this->formData( $item );

        $factor_item->update( [
            'date' => isset( $item[ 'date' ] ) ? Helper::vertaToMiladi( $item[ 'date' ] ) : '',
        ] );
    }
    return redirect()->back()->with( [
        'successByMessage' => 'مدارک با موفقیت بارگذاری شد.'
    ] );
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
        // Spatie: KYC docs arrive as temp media ids; attachTo replaces the single file
        // in each collection (native old-file cleanup, no deleteImage).
        if ( $request->urlimgmeli ) {
            \App\Helpers\MediaLibraryHelper::attachTo( $user, $request->urlimgmeli, 'imgmeli' );
        }
        if ( $request->urlimgshenasname ) {
            \App\Helpers\MediaLibraryHelper::attachTo( $user, $request->urlimgshenasname, 'imgshenasname' );
        }
        if ( $request->urlimgturnover ) {
            \App\Helpers\MediaLibraryHelper::attachTo( $user, $request->urlimgturnover, 'imgturnover' );
        }

        $this->formData( $request->item );

        $factor_item->update( [ 'status_factor' => 14 ] );

        return redirect()->back()->with( [
            'successByMessage' => 'مدارک با موفقیت بارگذاری شد.'
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
        foreach ( $factor_item->factoritemable->beforeforms as $key => $form ) {
            if ( $form->type == 'file' || $form->type == 'multi_file' ) {
                // Ajax temp upload: item['url'<name>] holds Spatie media id(s) — one for
                // 'file', a list for 'multi_file'. Move each into the factor item's own
                // collection, tagged with the form name so documentUrls(<name>) finds it.
                // factor_values is deliberately NOT written for file fields: the upload
                // lives in Spatie only, and any legacy path already in the column stays
                // untouched as the read fallback.
                $mediaIds = $item[ 'url' . $form->name ] ?? null;
                foreach ( (array) $mediaIds as $mediaId ) {
                    if ( $mediaId ) {
                        MediaLibraryHelper::attachTo(
                            $factor_item,
                            (string) $mediaId,
                            FactorItem::DOCUMENTS_COLLECTION,
                            [ 'field' => $form->name ]
                        );
                    }
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
                // Ajax temp upload: item['url'<name>] holds Spatie media id(s). These attach
                // to the TOTAL factor item — that is the model owning contract_values, and
                // the one every contract reader looks at. contract_values is not written.
                $totalFactorItem = $factor_item->totalFactorItem;
                $mediaIds = $item[ 'url' . $contract_form->name ] ?? null;
                if ( $totalFactorItem ) {
                    foreach ( (array) $mediaIds as $mediaId ) {
                        if ( $mediaId ) {
                            MediaLibraryHelper::attachTo(
                                $totalFactorItem,
                                (string) $mediaId,
                                TotalFactorItem::CONTRACT_COLLECTION,
                                [ 'field' => $contract_form->name ]
                            );
                        }
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
        $temp = in_array( $item->status_factor, array( 0, 4, 14, 3, 2, 9, 17 ) );
        //check totalFactorItem confirmation
        if ( $item->totalFactorItem && $item->totalFactorItem->confirm ) {
            $temp = false;
        }

        return $temp;
    }
}
