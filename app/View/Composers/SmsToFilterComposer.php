<?php

namespace App\View\Composers;

use App\Models\AdvertisingBalance;
use App\Models\GhorfeOnlineList;
use App\Models\SmsMessageTemplate;
use App\Services\Sms\SmsAccessTierService;
use Illuminate\View\View;

/**
 * Data for the "send SMS to this filter result" pop-up of the requests page: the business
 * the campaign would be sent from, its advertising lines, its templates and its credit.
 *
 * Mirrors SmsBulkSendController::resolveBusiness() so the pop-up and the endpoints it calls
 * always agree on the business.
 */
class SmsToFilterComposer
{
    public function compose(View $view): void
    {
        $user = auth()->user();
        $business = ghorfe();

        if (!$business && $user?->isSuperAdmin()) {
            $business = GhorfeOnlineList::first();
        }

        $senders = collect();
        $templates = collect();
        $balance = 0.0;

        if ($business) {
            $senders = $business->senderNumbers()
                ->where('sender_numbers.type', 'advertising')
                ->where('sender_numbers.status', 'active')
                ->get(['sender_numbers.id', 'sender_numbers.number']);

            $templates = SmsMessageTemplate::active()
                ->where('ghorfe_online_list_id', $business->id)
                ->latest()
                ->get(['id', 'title', 'content']);

            $balance = (float) (AdvertisingBalance::where('ghorfe_online_list_id', $business->id)->value('balance') ?? 0);
        }

        $view->with([
            'smsBusiness' => $business,
            'smsSenders' => $senders,
            'smsTemplates' => $templates,
            'smsBalance' => $balance,
            // Who sees the button at all: holders of view-phone-numbers (superAdmin always) who may also send group SMS.
            'smsCanSend' => (bool) $business && $user && app(SmsAccessTierService::class)->canViewPhoneNumbers($user)
                && ($user->isSuperAdmin() || $user->isAbleTo('send-group-sms') || $user->isAdmin()),
        ]);
    }
}
