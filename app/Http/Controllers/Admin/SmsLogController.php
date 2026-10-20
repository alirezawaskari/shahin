<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\SmsMessageResource;
use App\Models\GhorfeOnlineList;
use App\Models\Groupsms;
use App\Models\Setting;
use App\Services\Sms\SmsAccessTierService;
use App\Services\Sms\SmsConfidentialityGuard;
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

        $canUseOtp = app(SmsAccessTierService::class)->canUseOtp($user);

        $filters = $request->all();
        $messages = $this->logService->getPaginatedLogs($filters, $business, $isSuperAdmin, 25, $canUseOtp);
        $messages->appends($request->query());

        $summary = $this->logService->getSummaryMetrics($filters, $business, $isSuperAdmin, $canUseOtp);

        SmsConfidentialityGuard::redactMany($messages->getCollection(), $user);

        // Filter options for dropdowns
        $businesses = $isSuperAdmin ? GhorfeOnlineList::orderBy('title')->get() : collect([$business])->filter();

        $senderNumbersQuery = Setting::query()->where('key', 'sms_sender_number');
        if (!$isSuperAdmin && $business) {
            $senderNumbersQuery->where('settingable_type', GhorfeOnlineList::class)->where('settingable_id', $business->id);
        }
        $senderNumbers = $senderNumbersQuery->latest()->get();
        if ($senderNumbers->isEmpty()) {
            $defaultSender = Setting::where('key', 'sms_sender_number')->first()
                ?? Setting::where('key', 'iranSms_linenumber')->first();
            if ($defaultSender) {
                $senderNumbers = collect([$defaultSender]);
            }
        }

        $groups = \Illuminate\Support\Facades\Schema::hasColumn('groupsms', 'group')
            ? Groupsms::select('group')->distinct()->whereNotNull('group')->get()->map(fn ($r) => (object)[
                'id' => $r->group,
                'name' => $r->group,
                'subgroups' => collect(),
            ])
            : collect();

        $statuses = [
            'queued' => 'در صف ارسال',
            'sent' => 'ارسال شده به مخابرات',
            'delivered' => 'تحویل شده به گیرنده',
            'failed' => 'ناموفق / خطا',
        ];

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => SmsMessageResource::collection($messages->getCollection())->toArray($request),
                'meta' => [
                    'current_page' => $messages->currentPage(),
                    'last_page' => $messages->lastPage(),
                    'per_page' => $messages->perPage(),
                    'total' => $messages->total(),
                ],
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
            'filters',
            'canUseOtp'
        ));
    }

    /**
     * Display details of a specific SMS message.
     */
    public function show(Request $request, $smsMessage): View|JsonResponse
    {
        $smsMessage = $smsMessage instanceof Groupsms ? $smsMessage : Groupsms::findOrFail($smsMessage);
        $user = auth()->user();
        $isSuperAdmin = $user?->isSuperAdmin() ?? false;
        $business = ghorfe();

        if (!$isSuperAdmin && $business && $smsMessage->ghorfe_online_list_id !== $business->id) {
            abort(403, 'شما دسترسی به این پیامک را ندارید.');
        }

        // OTP rows are not visible to viewers who cannot use OTP, even by direct ID.
        abort_if(
            $smsMessage->service_type === 'otp' && !app(SmsAccessTierService::class)->canUseOtp($user),
            404
        );

        $smsMessage->load(['business', 'senderNumber', 'template', 'campaign', 'user', 'creator']);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => (new SmsMessageResource($smsMessage))->toArray($request),
            ]);
        }

        SmsConfidentialityGuard::redact($smsMessage, $user);
        app(\App\Services\Sms\CatalogBreadcrumbResolver::class)->annotateMessages([$smsMessage]);

        return view('Admin.sms.logs.show', compact('smsMessage'));
    }
}
