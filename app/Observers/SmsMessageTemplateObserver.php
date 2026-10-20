<?php

namespace App\Observers;

use App\Models\SmsTemplate;
use Illuminate\Support\Facades\Auth;

class SmsMessageTemplateObserver
{
    /**
     * Handle the SmsTemplate "saving" event.
     */
    public function saving(SmsTemplate $template): void
    {
        $group = $template->sms_message_group_id
            ? ($template->relationLoaded('group') ? $template->group : $template->group()->first())
            : null;

        $template->type = $group?->isConfidential() ? 'confidential' : 'normal';
    }

    /**
     * Handle the SmsTemplate "created" event.
     */
    public function created(SmsTemplate $template): void
    {
    }

    /**
     * Handle the SmsTemplate "updated" event.
     */
    public function updated(SmsTemplate $template): void
    {
    }

    /**
     * Handle the SmsTemplate "deleted" event.
     */
    public function deleted(SmsTemplate $template): void
    {
        //
    }

    /**
     * Handle the SmsTemplate "restored" event.
     */
    public function restored(SmsTemplate $template): void
    {
        //
    }

    /**
     * Handle the SmsTemplate "force deleted" event.
     */
    public function forceDeleted(SmsTemplate $template): void
    {
        //
    }
}
