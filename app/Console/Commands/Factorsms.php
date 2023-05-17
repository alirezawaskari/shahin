<?php

namespace App\Console\Commands;

use App\Models\Factor;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class Factorsms extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sms:factor';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'send sms to users with related factor status = false in the last 24 hours';

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
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function handle()
    {
        $headers = [
            'apikey' => config('services.sms.apikey'),
            'Content-Type' => 'application/x-www-form-urlencoded'
        ];
        $client = new Client([
            'headers' => $headers
        ]);
        //send sms expanding factor before the factor expiration
        $final_factors = Factor::whereDate('expired_at' , Carbon::now()->toDateString())->where('remember' , 0)->where('status' , '!=' , '1')->where('title' , '!=' , 'installment')->take(10)->get();
        if (count($final_factors) > 0){
            foreach ($final_factors as $factor) {
                    $body = [
                        'receptor' => $factor->user->phone,
                        'type' => 1,
                        'template' => 'finalrememberfactor',
                        'param1' => 'lalecard.ir/pf/'.$factor->resnumber,
                    ];
                    $client->request('POST', 'http://api.parsasms.com/v2/send/verify', [
                        'form_params' => $body
                    ]);
			        if(isset($factor->task) && $factor->task->status == 1)
                    {
                        $factor->task->update([
                            'status' => 6
                        ]);
                    }
                    $factor->update([
                        'remember' => 2
                    ]);
            }
        }
        //change factor status && task status to the expired after the factor expiration
        $expire_factors = Factor::where(DB::raw("(DATE_FORMAT(expired_at,'%Y-%m-%d %h:%m:%s'))"), '<=', now())->where('status' , '0')->take(100)->get();
        foreach ($expire_factors as $factor) {
            $factor->update([
                'remember' => 3,
				'status' => 3,
            ]);
            if(isset($factor->task) && $factor->task->status == 6)
            {
                $factor->task->update(['status' => 9]);
            }
        }
    }
}
