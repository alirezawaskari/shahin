<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Province;
use App\Models\SingleWebPage;
use App\Models\Service;
use App\Models\User;
use App\Models\ContractForm;
use App\Models\Disease;
use App\Models\Factor;
use App\Models\FactorItem;
use Illuminate\Support\Facades\View;

class SingleWebPageController extends Controller
{
    /**
     * @param SingleWebPage $SingleWebPage
     * @param User $callcenter
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application|\never
     */
    public function index(SingleWebPage $singleWebPage)
    {
        if ($singleWebPage->status){
            $singleWebPage->load('services');
            return view('site.singleWebPage.index', compact('singleWebPage'));
        }
        return abort(404);
    }


    /**
     * @param SingleWebPage $singleWebPage
     * @param Service $service
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function single(SingleWebPage $singleWebPage , Service $service )
    {
        $user = auth()->user();
        $financial_plans_types = $service->financialPlansTypes()->where('status' , 1)->where('is_show' , 1)->whereRaw('capacity > reserved')->where('end_date' , '>=' , now())->get();
        $registerForm = View::make('site.services.registerForm' , compact('service' , 'financial_plans_types' , 'callcenter'))->render();
        $content = str_replace('[form]', $registerForm, $service->content);

        if ($user) {
            $required = null;
            $founded = [];
            $requireServices = [];
            if (!is_null($service->required) && count($service->required) > 0) {
                foreach ($service->required as $item) {
                    $factor = $user->factors()->where('service_id', $item)->whereStatus(true)->first();
                    if ($factor){
                        array_push($founded , $factor->id);
                    }
                }
                $requireServices = Service::find($service->required);
                $required = false;
                if (!is_null($founded) && count($founded) == count($service->required)) {
                    $required = true;
                }
            }
            $check = $user->factors()->whereStatus(true)->whereHas('factorItems', function ($query) use ($service) {
                $query->where('factoritemable_id', $service->id);
            })->exists();

            $factor=null;

            if ($check) {
                if ($service->multiple) {
                    $check = false;
                }
                $factor=  $user->factors()->whereStatus(true)->whereHas('factorItems', function ($query) use ($service) {
                    $query->where('factoritemable_id', $service->id);
                })->first();
            }

            return view('site.singleWebPage.single', compact('content' , 'singleWebPage','financial_plans_types' ,'service', 'required', 'requireServices' , 'check','factor'));
        }

        $provinces = Province::get();
        $citys = City::get();
        return view('site.singleWebPage.single' , compact('content' , 'singleWebPage','financial_plans_types' , 'service' , 'provinces' , 'citys'));
    }

    public function checkout(SingleWebPage $singleWebPage , Factor $factor ) {
        $user = auth()->user();
        $diseases=Disease::all();
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

    return view( 'site.singleWebPage.checkout', compact( 'singleWebPage' ,'factor', 'items', 'check', 'user' ,'diseases') );
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
