<?php

namespace App\Console\Commands;

use App\Services\Sms\BulkSmsSendService;
use Illuminate\Console\Command;

class DispatchDueSmsCampaigns extends Command
{
    protected $signature = 'sms:dispatch-due-campaigns';

    protected $description = 'Send scheduled bulk SMS campaigns whose scheduled time has arrived';

    /**
     * Production requirement: the default QUEUE_CONNECTION=sync ignores the job delay, so this
     * command (run every minute by the scheduler, which in turn needs the
     * "* * * * * php artisan schedule:run" cron entry) is what actually sends scheduled campaigns.
     * It is idempotent: a per-campaign lock plus the status check in executeCampaign() make a
     * second run (or a late delayed queue job) a no-op.
     */
    public function handle(BulkSmsSendService $bulkSmsSendService): int
    {
        $picked = $bulkSmsSendService->dispatchDueCampaigns();

        $this->info("Due SMS campaigns processed: {$picked}");

        return self::SUCCESS;
    }
}
