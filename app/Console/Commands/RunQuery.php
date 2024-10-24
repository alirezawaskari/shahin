<?php

namespace App\Console\Commands;

use App\Models\FactorItem;
use Illuminate\Console\Command;
use App\Classes\External\IranSms;
use App\Models\Factor;
use App\Models\Session;

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
        $factors = Factor::where('coupon_id' , '!=' , null)->get();
        foreach($factors as $factor)
        {
            if(isset($factor->coupon->discount) && $factor->price_without_discount == 0)
            {
                $price_with_coupon_discount = $factor->price - ($factor->price * $factor->coupon->discount / 100);
                $factor->update( [
                    'price_without_discount' => $factor->price,
                    'price' => $price_with_coupon_discount,
               ] );
            }
        }
        dd('success');

        // $sessions = Session::whereNotNull('user_performance_id')->get();
        // foreach($sessions as $session)
        // {
        //     $session->userPerformances()->syncWithoutDetaching([
        //         'user_performance_id' => $session->user_performance_id,
        //     ]);
        // }
        //close factorItems
        // $factor_items = FactorItem::whereIn( 'status_factor', [ 0, 1, 2, 3, 4, 5, 8, 9, 11, 12, 13, 14 ] )->take( 400 )->get();
        // foreach ( $factor_items as $factor_item ) {
        //     if ( $factor_item->factor && $factor_item->factor->status == 1 && $factor_item->factor->user && $factor_item->factoritemable && $factor_item->factoritemable->category_id != 421 ) {
        //         // event( new factorItemClosing( $factor_item->factor->user, $factor_item ) );

        //         $phones = $factor_item->factor->user->phone;
        //         $body = [
        //             'param1' => $factor_item->factor->user->name.' '.$factor_item->factor->user->family,
        //             'param2' => 'درخواست تسهیلات خرید کالا یا خدمات اقساطی ',
        //         ];
        //         IranSms::sendSingleSms( 'factoritemClosing', $phones, $body );

        //         $factor_item->update( [ 'status_factor' => 15 ] );
        //         $factor_item->totalFactoItem ? $factor_item->totalFactoItem->update( [ 'total_factor_item_status_id'=>15 ] ) : '';

        //         $factor_item->factor->comments()->create( [
        //             'user_id' => auth()->user()->id,
        //             'comment' => 'به دلیل عدم پیگیری پرونده مختومه گردید.',
        //         ] );
        //     } else {
        //         $factor_item->update( [ 'status_factor' => 15 ] );
        //     }
        // }
    }

}
