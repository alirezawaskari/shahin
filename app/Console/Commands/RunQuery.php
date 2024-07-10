<?php

namespace App\Console\Commands;

use App\Models\BeforeFormItem;
use App\Models\Center;
use App\Models\ContractForm;
use App\Models\Factor;
use App\Models\FactorItem;
use App\Models\FactorItemInstallment;
use App\Models\TotalFactorItem;
use App\Models\TotalFactorItemInstallment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Classes\External\IranSms;
use App\Events\factorItemClosing;

class RunQuery extends Command {
    /**
    * The name and signature of the console command.
    *
    * @var string
    */
    protected $signature = 'command:query';

    /**
    * The console command description.
    *
    * @var string
    */
    protected $description = 'run query for new admin panel';

    /**
    * Create a new command instance.
    *
    * @return void
    */

    public function __construct() {
        parent::__construct();
    }

    /**
    * Execute the console command.
    *
    * @return mixed
    */

    public function handle() {

            $factor_items = FactorItem::whereIn('status_factor' , [0,1,2,3,4,5,8,9,11,12,13,14])->take(400)->get();
            foreach($factor_items as $factor_item)
            {
                if($factor_item->factor && $factor_item->factor->user && $factor_item->factoritemable->category_id != 421)
                {
                   // event(new factorItemClosing($factor_item->factor->user , $factor_item));

                    $phones = $factor_item->factor->user->phone;
                    $body = [
                        'param1' => $factor_item->factor->user->name.' '.$factor_item->factor->user->family,
                        'param2' => $factor_item->factoritemable->title,
                    ];
                    IranSms::sendSingleSms('factoritemClosing',$phones,$body);

                    $factor_item->update( [ 'status_factor' => 15 ] );
                    $factor_item->totalFactoItem ? $factor_item->totalFactoItem->update(['total_factor_item_status_id'=>15]) : '';

                    $factor_item->factor->comments()->create( [
                        'user_id' => auth()->user()->id,
                        'comment' => 'به دلیل عدم پیگیری پرونده مختومه گردید.',
                    ] );
                }
            }
           dd('success');

        /*
        1-@  add total_factor_item_id integer field to the factors table //nullable
        2-@ convert factor_item_id to factor_item_attribute_able_id & factor_item_attribute_able_type in factor_item_attributes table
        3-@ convert service_id to factoritemable_id & factoritemable_type in factor_items table
        4-@  add check integer field to the factor_item_installments table for run query
        5-  import new 'permissions' table & 'before_forms' table & 'categories' & 'factor_item_products' table
        6-@  add 'file_number_mali' (string) in 'total_factor_items' table
        7-@  add identifier_code string field by null value to the users table
        8-@  add 'sms_logs' table
        9-@  add 'phone' and 'background_image' in center table
        10-@ add 'user_id' in 'center_product' table
        11-@ add 'product_validations' table
        12-@ add 'validations' table
        13-@ add 'storage_store_room_id' in 'pack_store_room' table
        14-@ add status in 'color' table
        15-@ set default value to 'status' in 'Articles' table
        16-@ add 'category_news_id' in 'news' table
        17-@ add 'pay_type' in 'coupons' table
        18-@ add 'purchase_type' in 'coupons' table
        19-@ change to nullable 'address' in 'center_requests' table
        20-@ update field in services , centers , service_items , products , work_samples
        21-@ add filed coupon_discount in factor_items table
        22-@ add deleted_at to the factor_item_service_tariffs tables
        23-@ import log_messages table
        */


        // $total_factor_items = TotalFactorItem::whereNotNull('contract_values')->get();
        // foreach($total_factor_items as $total_factor_item)
        // {
        //     $description_request_contract_settlement = isset($total_factor_item->contract_values['description_request_contract_settlement']) ? $total_factor_item->contract_values['description_request_contract_settlement'] : '';
        //     $img_request_contract_settlement = isset($total_factor_item->contract_values['request_contract_settlement']) ? $total_factor_item->contract_values['request_contract_settlement'] : '';
        //     $img_warranty_delivery_receipt = isset($total_factor_item->contract_values['imgcertificateemploymentguaranteed_delivery_image']) ? $total_factor_item->contract_values['imgcertificateemploymentguaranteed_delivery_image'] : '';
        //     if($description_request_contract_settlement || $img_request_contract_settlement)
        //     {
        //         $total_factor_item->totalFactorItemContractSettlement()->create([
        //             'description_request_contract_settlement' => $description_request_contract_settlement,
        //             'img_request_contract_settlement' => $img_request_contract_settlement,
        //             'img_warranty_delivery_receipt' => $img_warranty_delivery_receipt,
        //         ]);
        //     }
        // }
        //add position to the contractForm table
        //ContractForm::where('position' , 0)->update(['position' => DB::raw("id")]);
        //convert App\Service to App\Models\Service
        // Center::where('centerable_type' , 'App\Service')->update(['centerable_type' => 'App\Models\Service']);
        // FactorItem::whereNull('factoritemable_type' )->update(['factoritemable_type' => 'App\Models\Service']);

        //convert App\BeforeForm to App\Models\BeforeForm
        // BeforeFormItem::where('beforeformitemable_type' , 'App\BeforeForm')->update(['beforeformitemable_type' => 'App\Models\BeforeForm']);
        //fill total_factor_item table for installment factor_items
        // $factor_items = FactorItem::where( 'type', 1 )->where( 'total_factor_item_id', null )->get();
        // foreach ( $factor_items as $factor_item ) {

        //     if($factor_item->id == $factor_item->supervisor_id)
        //     {
        //         $total_factor_item = TotalFactorItem::create( [
        //             'factor_id' => isset( $factor_item->factor ) ? $factor_item->factor->id : 1,
        //             'supporter_id' => $factor_item->supporter_id,
        //             'status_mali' => $factor_item->status_mali,
        //             'contract_values' => $factor_item->contract_values,
        //             'factor_values' => $factor_item->factor_values,
        //             'credit' => $factor_item->credit,
        //             'guarantee_type' => $factor_item->guarantee_type,
        //             'total_price' => $factor_item->total_price,
        //             'installments_num' => $factor_item->installments_num,
        //             'total_factor_item_status_id' => $factor_item->status_factor,
        //             'percent_fines' => $factor_item->percent_fines,
        //             'confirm' => $factor_item->confirm,
        //             'file_number_mali' => $factor_item->file_number_mali,
        //             'remember' => $factor_item->remember,
        //             'approval_date' => $factor_item->approval_date,
        //         ] );
        //         $total_factor_item->property()->create([
        //             'prepayment_price'=>$factor_item->property()->exists()?$factor_item->property->prepayment_price:0,
        //             'prepayment_debt'=>$factor_item->property()->exists()?$factor_item->property->prepayment_debt:0,
        //             'prepayment_count'=>$factor_item->property()->exists()?$factor_item->property->prepayment_count:0,
        //             'prepayment_num'=>$factor_item->property()->exists()?$factor_item->property->prepayment_num:0,
        //             'price_difference_installment'=>$factor_item->property()->exists()?$factor_item->property->price_difference_installment:0,
        //             'price_difference_total'=>$factor_item->property()->exists()?$factor_item->property->price_difference_total:0,
        //             'price_difference_debt'=>$factor_item->property()->exists()?$factor_item->property->price_difference_debt:0,
        //         ]);
        //         $factor_item->update( [ 'total_factor_item_id' => $total_factor_item->id ] );
        //     }
        // }

        //create total_factor_item_installment table
        //fill total_factor_item_installment table by data factor_item_installment table
        // $factor_item_insallments = FactorItemInstallment::where('check' , false)->get();
        // foreach ( $factor_item_insallments as $factor_item_insallment ) {
        //     if ( isset( $factor_item_insallment->factoritem->total_factor_item_id) ) {
        //         TotalFactorItemInstallment::create( [
        //             'price' => $factor_item_insallment->price,
        //             'level' => $factor_item_insallment->level,
        //             'factor_id' => $factor_item_insallment->factor_id,
        //             'total_factor_item_id' => $factor_item_insallment->factoritem->total_factor_item_id,
        //             'date' => $factor_item_insallment->date,
        //             'pay_date' => $factor_item_insallment->pay_date,
        //             'status' => $factor_item_insallment->status,
        //             'resnumber' => $factor_item_insallment->resnumber,
        //             'remember' => $factor_item_insallment->remember,
        //         ] );
        //         $factor_item_insallment->update(['check' => true]);
        //     }
        // }

        //fill total_factor_item_id in factors table
        // $factors = Factor::where( 'total_factor_item_id', null )->where( 'factor_item_id', '!=', null )->get();
        // foreach ( $factors as $factor ) {
        //     if ( $factor->factorItem && $factor->factorItem->type )
        //     {
        //         $factor->update( [
        //             'total_factor_item_id' => $factor->factorItem->total_factor_item_id,
        //         ] );
        //     }
        // }

        // $factor_items = FactorItem::where( 'service_id', 1 )->where( 'type', 1 )->where( 'is_credit', false )->get();
        // foreach ( $factor_items as $factor_item ) {
        //     if ( $factor_item->factor ) {
        //         $user = $factor_item->factor->user;
        //         $type = $factor_item->type;

        //         //get total price factor
        //         $total_price = 0;
        //         $other_price = isset( $factor_item->factor_value[ 'otherPrice' ] ) ? $factor_item->factor_value[ 'otherPrice' ] : 0;
        //         $total_price = self::getTotalPrice( $factor_item, $total_price, $other_price );

        //         //get supervisor factorItem
        //         $supervisor_factor_item = $factor_item->supervisor;
        //         $sessions_sum_price = FactorItem::getSessionsSumPrice( $factor_item, 'all' );

        //         //get difference price
        //         if ( $factor_item->supervisor->confirm ) {
        //             $price_difference_paid = isset( $factor_item->price_difference[ 'total' ] ) ? ( $factor_item->price_difference[ 'total' ] - $factor_item->price_difference[ 'debt' ] ) : 0;
        //             $credit = ( $price_difference_paid + $factor_item->total_price + $factor_item->discount ) - $sessions_sum_price;
        //             $supervisor_factor_item->update( [ 'credit' => $credit ] );
        //             self::calculateDiffrencePrice( $supervisor_factor_item, $total_price );
        //         } else {
        //             //calculate
        //             $supervisor_factor_item->update( [ 'credit' => $sessions_sum_price * ( -1 ) ] );
        //             self::calculate( $supervisor_factor_item, $total_price, $type, $user );
        //         }
        //     }
        //     $factor_item->update( [ 'is_credit' => true ] );
        // }
        // return true;

        // $factor_items = FactorItem::where( 'type', 1 )->where( 'confirm', true )->where( 'service_id', 1 )->where( 'is_credit', 0 )->get();
        // foreach ( $factor_items as $item ) {
        //     $sessins_sum = 0;
        //     $factor_items_col = FactorItem::getFatorItemCollection( $item )->get();
        //     if ( $item->id == $item->supervisor_id ) {
        //         $price_difference_paid = isset( $item->price_difference[ 'total' ] ) ? ( $item->price_difference[ 'total' ] - $item->price_difference[ 'debt' ] ) : 0;
        //         foreach ( $factor_items_col as $factorItem ) {
        //             $sessins_sum =  $sessins_sum + $factorItem->sessions->where( 'status', 1 )->sum( 'price' );
        //         }
        //         $credit = $price_difference_paid + $item->total_price - $sessins_sum;
        //         $item->update( [ 'credit' => $credit, 'is_credit' => 1 ] );
        //     }
        // }



    }


}
