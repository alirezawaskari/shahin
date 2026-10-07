<?php

namespace App\Observers;

use App\Models\SmsMessageTemplate;
use App\Models\SmsMessageTemplateHistory;
use Illuminate\Support\Facades\Auth;

class SmsMessageTemplateObserver
{
    /**
     * Handle the SmsMessageTemplate "created" event.
     */
    public function created(SmsMessageTemplate $template): void
    {
        // Wave 2 worker will wire up full recording logic; stub records initial snapshot when possible.
        if (Auth::id()) {
            SmsMessageTemplateHistory::create([
                'sms_message_template_id' => $template->id,
                'content' => $template->content,
                'changed_by' => Auth::id(),
            ]);
        }
    }

    /**
     * Handle the SmsMessageTemplate "updated" event.
     */
    public function updated(SmsMessageTemplate $template): void
    {
        // Wave 2 worker will wire up full recording logic; stub records changed snapshot if content was modified.
        if ($template->wasChanged('content') && (Auth::id() || $template->updated_by)) {
            SmsMessageTemplateHistory::create([
                'sms_message_template_id' => $template->id,
                'content' => $template->content,
                'changed_by' => Auth::id() ?: $template->updated_by,
            ]);
        }
    }

    /**
     * Handle the SmsMessageTemplate "deleted" event.
     */
    public function deleted(SmsMessageTemplate $template): void
    {
        //
    }

    /**
     * Handle the SmsMessageTemplate "restored" event.
     */
    public function restored(SmsMessageTemplate $template): void
    {
        //
    }

    /**
     * Handle the SmsMessageTemplate "force deleted" event.
     */
    public function forceDeleted(SmsMessageTemplate $template): void
    {
        //
    }
}
