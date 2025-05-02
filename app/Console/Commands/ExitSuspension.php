<?php

namespace App\Console\Commands;
use App\Models\FactorItem;
use App\Models\Callcenter;
use App\Models\TotalFactorItem;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ExitSuspension extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'exit:suspension';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'change the status of suspended items at the expired_at time';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //change status totalFactorItems at the expired_at
        TotalFactorItem::where('total_factor_item_status_id', 3)->where(DB::raw("(DATE_FORMAT(expired_at,'%Y-%m-%d %h:%m:%s'))"), '<=', now())->update(['total_factor_item_status_id' => 14]);

        //change status_factor factorItems at the expired_at
        FactorItem::where( 'status_factor', 3 )->where( DB::raw("(DATE_FORMAT(expired_at,'%Y-%m-%d %h:%m:%s'))"), '<=', now() )->take(100)->update( [ 'status_factor' => 14 ] );

        //change status callcenters at the expired_at
        Callcenter::where('status' , 2)->whereBetween('date_expire',[now()->subDays(5), now()])->take(100)->update(['status' => 4]);

        //archive callcenters
        Callcenter::where('status' , 2)->where(DB::raw("(DATE_FORMAT(date_expire,'%Y-%m-%d %h:%m:%s'))") , '>' , now()->addMonths(11))->take(100)->update(['status' => 8]);
    }
}
