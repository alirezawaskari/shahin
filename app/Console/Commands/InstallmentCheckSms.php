<?php

namespace App\Console\Commands;

use App\Events\checkSmsReminder;
use App\Models\Check;
use Carbon\Carbon;
use Illuminate\Console\Command;

class InstallmentCheckSms extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'installmentCheck:sms';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        $checks = Check::whereDate('date',Carbon::today()->addDays(3))->where('title','installment')->get();
        foreach ($checks as $check){
            event(new checkSmsReminder( $check->checkable->factor->user,$check));
        }
    }
}
