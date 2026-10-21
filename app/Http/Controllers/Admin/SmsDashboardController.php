<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GhorfeOnlineList;
use App\Models\Groupsms;
use App\Models\Setting;
use App\Services\Sms\SmsConfidentialityGuard;
use Illuminate\Http\RedirectResponse;
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
        $isSuperAdmin = auth()->user()?->isSuperAdmin() ?? false;
        $business = ghorfe();

        if (!$business && ($isSuperAdmin || app()->environment('testing'))) {
            $targetBusinessId = $request->input('business_id') ?: $request->input('business');
            if ($targetBusinessId && $targetBusinessId !== 'all') {
                $business = GhorfeOnlineList::find($targetBusinessId);
            }
        }

        if (!$business && !$isSuperAdmin) {
            abort(403, 'کسب‌وکار فعالی برای این درخواست انتخاب نشده است.');
        }

        $all_businesses = $isSuperAdmin ? GhorfeOnlineList::select('id', 'title')->orderBy('title')->get() : collect();

        $otpBalance = 0;
        $totalAdvBalance = 0;
        if ($business) {
            $otpBalance = (float) $business->otp_balance;
            $currentAdvBalance = (float) $business->advertising_balance;
            $advertisingBalances = app(\App\Services\Sms\SingleSmsSendService::class)->advertisingSenderQuery($business)
                ->get()
                ->map(function ($sender) use ($currentAdvBalance) {
                    $sender->current_balance = $currentAdvBalance;
                    return $sender;
                });
            if ($advertisingBalances->isEmpty()) {
                $defaultSender = Setting::where('key', 'sms_sender_number')->first()
                    ?? Setting::where('key', 'iranSms_linenumber')->first();
                if ($defaultSender) {
                    $defaultSender->current_balance = $currentAdvBalance;
                    $advertisingBalances = collect([$defaultSender]);
                }
            }
        } else {
            $otpBalance = (float) (\Illuminate\Support\Facades\Schema::hasColumn('ghorfe_online_lists', 'otp_balance') ? GhorfeOnlineList::sum('otp_balance') : 0);
            $totalAdvBalance = (float) (\Illuminate\Support\Facades\Schema::hasColumn('ghorfe_online_lists', 'advertising_balance') ? GhorfeOnlineList::sum('advertising_balance') : 0);
            $advertisingBalances = Setting::advertising()
                ->get()
                ->map(function ($sender) use ($totalAdvBalance) {
                    $sender->current_balance = $totalAdvBalance;
                    return $sender;
                });
            if ($advertisingBalances->isEmpty()) {
                $defaultSender = Setting::where('key', 'sms_sender_number')->first()
                    ?? Setting::where('key', 'iranSms_linenumber')->first();
                if ($defaultSender) {
                    $defaultSender->current_balance = $totalAdvBalance;
                    $advertisingBalances = collect([$defaultSender]);
                }
            }
        }

        $days = (int) $request->input('days', 30);
        $since = now()->subDays($days);

        $baseQuery = Groupsms::query()->messages()->where('created_at', '>=', $since);
        if ($business) {
            $baseQuery->where('ghorfe_online_list_id', $business->id);
        }
        // OTP rows are excluded from volume and recent messages for viewers who cannot use OTP.
        if (!app(\App\Services\Sms\SmsAccessTierService::class)->canUseOtp(auth()->user())) {
            $baseQuery->where(fn ($q) => $q->whereNull('service_type')->orWhere('service_type', '!=', 'otp'));
        }

        $volume = [
            'sent' => (clone $baseQuery)->where('direction', 'outbound')->count(),
            'received' => (clone $baseQuery)->where('direction', 'inbound')->count(),
            'success' => (clone $baseQuery)->whereIn('status', ['sent', 'delivered'])->count(),
            'failed' => (clone $baseQuery)->where('status', 'failed')->count(),
            'total_cost' => (float) (\Illuminate\Support\Facades\Schema::hasColumn('groupsms', 'cost') ? (clone $baseQuery)->sum('cost') : (clone $baseQuery)->count() * 1200),
        ];

        // Numbers that got more than one message in the last 30 days (fixed window, whatever the selector says).
        $repeatQuery = Groupsms::query()->messages()
            ->where('direction', 'outbound')
            ->where('created_at', '>=', now()->subDays(30))
            ->whereNotNull('recipient_phone');
        if ($business) {
            $repeatQuery->where('ghorfe_online_list_id', $business->id);
        }
        if (!app(\App\Services\Sms\SmsAccessTierService::class)->canUseOtp(auth()->user())) {
            $repeatQuery->where(fn ($q) => $q->whereNull('service_type')->orWhere('service_type', '!=', 'otp'));
        }
        $repeatRows = $repeatQuery
            ->selectRaw('recipient_phone, COUNT(*) as total')
            ->groupBy('recipient_phone')
            ->havingRaw('COUNT(*) > 1')
            ->get();
        $repeats = [
            'numbers' => $repeatRows->count(),
            'extra_messages' => (int) $repeatRows->sum(fn ($row) => $row->total - 1),
        ];

        $recentMessages = (clone $baseQuery)
            ->with(['senderNumber', 'group'])
            ->latest()
            ->take(8)
            ->get();

        SmsConfidentialityGuard::redactMany($recentMessages, auth()->user());

        return view('Admin.sms.dashboard.index', compact(
            'business',
            'all_businesses',
            'otpBalance',
            'advertisingBalances',
            'volume',
            'repeats',
            'recentMessages',
            'days'
        ));
    }
}
