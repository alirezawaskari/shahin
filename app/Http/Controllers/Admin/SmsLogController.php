<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GhorfeOnlineList;
use App\Models\SenderNumber;
use App\Models\SmsMessage;
use App\Models\SmsMessageGroup;
use App\Services\Sms\SmsLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SmsLogController extends Controller
{
    public function __construct(
        protected SmsLogService $logService
    ) {
    }

    /**
     * Display a unified log listing of sent and received SMS messages.
     */
    public function index(Request $request): View|JsonResponse
    {
        $user = auth()->user();
        $isSuperAdmin = $user?->isSuperAdmin() ?? false;
        $business = ghorfe();

        if (!$business && $isSuperAdmin && $request->filled('business_id')) {
            $business = GhorfeOnlineList::find($request->input('business_id'));
        }

        $filters = $request->all();
        $messages = $this->logService->getPaginatedLogs($filters, $business, $isSuperAdmin, 25);
        $messages->appends($request->query());

        $summary = $this->logService->getSummaryMetrics($filters, $business, $isSuperAdmin);

        // Filter options for dropdowns
        $businesses = $isSuperAdmin ? GhorfeOnlineList::orderBy('title')->get() : collect([$business])->filter();

        $senderNumbersQuery = SenderNumber::query();
        if (!$isSuperAdmin && $business) {
            $senderNumbersQuery->whereHas('businesses', fn ($q) => $q->where('ghorfe_online_lists.id', $business->id));
        }
        $senderNumbers = $senderNumbersQuery->orderBy('number')->get();

        $groups = SmsMessageGroup::whereNull('parent_id')->with('subgroups')->orderBy('name')->get();

        $statuses = [
            'queued' => 'در صف ارسال',
            'sent' => 'ارسال شده به مخابرات',
            'delivered' => 'تحویل شده به گیرنده',
            'failed' => 'ناموفق / خطا',
        ];

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $messages,
                'summary' => $summary,
            ]);
        }

        return view('Admin.sms.logs.index', compact(
            'messages',
            'summary',
            'businesses',
            'senderNumbers',
            'groups',
            'statuses',
            'business',
            'isSuperAdmin',
            'filters'
        ));
    }

    /**
     * Display details of a specific SMS message.
     */
    public function show(Request $request, SmsMessage $smsMessage): View|JsonResponse
    {
        $user = auth()->user();
        $isSuperAdmin = $user?->isSuperAdmin() ?? false;
        $business = ghorfe();

        if (!$isSuperAdmin && $business && $smsMessage->ghorfe_online_list_id !== $business->id) {
            abort(403, 'شما دسترسی به این پیامک را ندارید.');
        }

        $smsMessage->load(['business', 'senderNumber', 'template', 'group', 'campaign', 'user', 'creator', 'request']);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $smsMessage,
            ]);
        }

        return view('Admin.sms.logs.show', compact('smsMessage'));
    }
}
