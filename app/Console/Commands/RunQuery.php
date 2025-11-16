<?php

namespace App\Console\Commands;

use App\Classes\External\SendSms;
use App\Models\FactorItem;
use Illuminate\Console\Command;
use App\Models\Callcenter;
use App\Models\ServiceTariff;

class RunQuery extends Command
{
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
        $service_tariffs = ServiceTariff::all();
        foreach($service_tariffs as $item)
        {
            $price = $item->purchase_price + ($item->price * 0.2);
            $item->update(['consumer_price' => $item->price]);
            if($price < $item->price)
            {
                $item->update(['price' => $price]);
            }
        }
        dd('success');
    }
}
