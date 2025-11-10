<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FactorItem;
use App\Models\FactorItemServiceTariff;
use App\Models\TotalFactorItem;
use Illuminate\Support\Facades\Log;

class FactorItemServiceTariffController extends Controller {
    /**
    * @param FactorItemServiceTariff $factorItemServiceTariff
    * @return \Illuminate\Http\JsonResponse
    */

    public function destroy( FactorItemServiceTariff $factorItemServiceTariff ) {
        $factor_item_service_tariff = $factorItemServiceTariff;
        $factor_item = $factor_item_service_tariff->factorItem;
        $type = $factor_item->type;
        if ( $factor_item_service_tariff->sessions->isEmpty() ) {
            $factor_item_service_tariff->delete();

            //get total price factor
            $total_price = 0;
            $other_price = isset( $factor_item->factor_value[ 'otherPrice' ] ) ? $factor_item->factor_value[ 'otherPrice' ] : 0;
            $total_price = FactorItem::getTotalPrice( $factor_item, $total_price, $other_price );

            //get supervisor factorItem
            $supervisor_factor_item = $factor_item->supervisor;

            //calculate financial information
            FactorItem::calculateFinancialInformation($supervisor_factor_item , $total_price, $type, $supervisor_factor_item->factor->user);
     
            if ( $supervisor_factor_item->totalFactorItem ) {
                TotalFactorItem::updateCalculate( $supervisor_factor_item->totalFactorItem );
            }
            $factor_item->update( [
                'complete' => false
            ] );
            Log::channel( 'db' )->info( 'service tariffs deleted.', [ 'factor_item_id' => $factor_item->id, 'registerar_id' => auth()->user()->id, 'service_tariffs' => $factor_item->serviceTariffs ] );

            $arr = array( 'msg' => 'باموفقیت حذف شد' );
            return Response()->json( $arr );
        }

        $arr = array( 'errors' => 'قبلا برای این خدمت جلسه ثبت شده است.' );
        return Response()->json( $arr );
    }
}
