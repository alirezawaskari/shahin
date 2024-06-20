<?php

namespace App\Console\Commands;

use App\Events\checkSmsReminder;
use App\Models\Check;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ManagePendingChecks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pendingCheck:mange';

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
        //chenge status pending checks
        $pending_checks = Check::whereDate('date','<=',Carbon::today()->addDays(3))->where('title','!=','guaranty')->where('status', 5);
        $pending_checks->update(['status' => 0]);
        //send sms to the applicant installment pending checks
        $installment_pending_checks = $pending_checks->where('title' , 'installment');
        foreach ($installment_pending_checks as $item){
            event(new checkSmsReminder( $item->applicantable->phone,$item));
        }
    }
}
