<?php

namespace App\Console\Commands;

use App\Classes\External\IranSms;
use App\Classes\External\SendSms;
use Illuminate\Console\Command;

class CheckCredit extends Command
{
    protected $signature = 'check:credit-sms-panel';
    protected $description = 'Check Credit SMS Panel';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $credit = (new IranSms())->fetchCredit()->credit;
        if ($credit <= 500000) {
            $params = [
                'params1' => $credit
            ];
            (new SendSms())->sendSingleSms("checkcreditsmspanel","09386682995",$params);
            $this->info('Renewal SMS sent');
        } else {
            $this->info('The credit was sufficient.');
        }
    }
}
