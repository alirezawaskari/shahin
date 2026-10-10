<?php

namespace App\Observers;

use App\Models\SmsMessageTemplate;
use App\Models\SmsMessageTemplateHistory;
use Illuminate\Support\Facades\Auth;

class SmsMessageTemplateObserver
{
    /**
     * Handle the SmsMessageTemplate "saving" event.
     *
     * `type` (confidential vs normal) is NEVER accepted from a form field —
     * it is always (re)computed here from the linked sms_message_group_id,
     * the same way SmsMessage::isConfidential() derives it from its group.
     * This runs on every create/update, so even a mass-assigned `type`
     * value (the model is $guarded = []) is overwritten before it persists.
     */
    public function saving(SmsMessageTemplate $template): void
    {
        $group = $template->sms_message_group_id
            ? ($template->relationLoaded('group') ? $template->group : $template->group()->first())
            : null;

        $template->type = $group?->isConfidential() ? 'confidential' : 'normal';
    }

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
