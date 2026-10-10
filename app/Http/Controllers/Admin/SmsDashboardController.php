<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdvertisingBalance;
use App\Models\OtpBalance;
use App\Models\SmsMessage;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * SMS module overview/dashboard (task.md §19 lists it under the "پیامک"
 * menu). Shows, for the current business: OTP balance, advertising
 * balance per sender number, recent message volume broken down by
 * sent/received and success/fail, and quick links into the rest of the
 * module. No dedicated dashboard existed before this.
 */
class SmsDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $business = ghorfe() ?? abort(403, 'کسب‌وکار فعالی برای این درخواست انتخاب نشده است.');

        $otpBalance = (float) (OtpBalance::where('ghorfe_online_list_id', $business->id)->value('balance') ?? 0);

        $advertisingBalances = $business->senderNumbers()
            ->where('sender_numbers.type', 'advertising')
            ->get()
            ->map(function ($sender) use ($business) {
                $sender->current_balance = (float) (AdvertisingBalance::where('ghorfe_online_list_id', $business->id)
                    ->where('sender_number_id', $sender->id)
                    ->value('balance') ?? 0);
                return $sender;
            });

        $days = (int) $request->input('days', 30);
        $since = now()->subDays($days);

        $baseQuery = SmsMessage::where('ghorfe_online_list_id', $business->id)
            ->where('created_at', '>=', $since);

        $volume = [
            'sent' => (clone $baseQuery)->where('direction', 'outbound')->count(),
            'received' => (clone $baseQuery)->where('direction', 'inbound')->count(),
            'success' => (clone $baseQuery)->whereIn('status', ['sent', 'delivered'])->count(),
            'failed' => (clone $baseQuery)->where('status', 'failed')->count(),
            'total_cost' => (float) (clone $baseQuery)->sum('cost'),
        ];

        $recentMessages = (clone $baseQuery)
            ->with(['senderNumber', 'group'])
            ->latest()
            ->take(8)
            ->get();

        return view('Admin.sms.dashboard.index', compact(
            'business',
            'otpBalance',
            'advertisingBalances',
            'volume',
            'recentMessages',
            'days'
        ));
    }
}
