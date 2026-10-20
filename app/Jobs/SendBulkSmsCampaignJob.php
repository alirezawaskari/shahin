<?php

namespace App\Jobs;

use App\Models\Groupsms;
use App\Services\Sms\BulkSmsSendService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendBulkSmsCampaignJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Groupsms $campaign
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(BulkSmsSendService $bulkSmsSendService): void
    {
        $bulkSmsSendService->executeCampaign($this->campaign);
    }
}
