<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\SmsMessageResource;
use App\Models\GhorfeOnlineList;
use App\Models\SenderNumber;
use App\Models\SmsMessage;
use App\Models\SmsMessageGroup;
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

        // Confidentiality enforcement (task.md §13): the body of a message in a
        // confidential group (مالی/حقوقی) is removed server-side for anyone
        // without the view-confidential-sms permission, for both the JSON
        // response below AND the server-rendered Blade table — mutating the
        // attribute here means the raw text is never serialized to the client
        // in either path, rather than merely hidden behind an @if in the view.
        SmsConfidentialityGuard::redactMany($messages->getCollection(), $user);

        // Filter options for dropdowns
        $businesses = $isSuperAdmin ? GhorfeOnlineList::orderBy('title')->get() : collect([$business])->filter();

        $senderNumbersQuery = SenderNumber::query();
        if (!$isSuperAdmin && $business) {
            $senderNumbersQuery->whereHas('businesses', fn ($q) => $q->where('ghorfe_online_lists.id', $business->id));
        }
        $senderNumbers = $senderNumbersQuery->orderBy('number')->get();

        // Subject groups belong to a business: pin to the active one so the dropdown never lists the
        // same group once per business (super-admin 'all businesses' view keeps every business).
        $groups = SmsMessageGroup::whereNull('parent_id')
            ->when($business, fn ($q) => $q->where('ghorfe_online_list_id', $business->id))
            ->with(['business', 'subgroups' => fn ($q) => $q->when($business, fn ($sq) => $sq->where('ghorfe_online_list_id', $business->id))])
            ->orderBy('name')
            ->get();

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
    public function show(Request $request, SmsMessage $smsMessage): View|JsonResponse
    {
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

        $smsMessage->load(['business', 'senderNumber', 'template', 'group', 'campaign', 'user', 'creator', 'request']);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => (new SmsMessageResource($smsMessage))->toArray($request),
            ]);
        }

        // Also redact for the server-rendered view: confidentiality is
        // enforced regardless of output format.
        SmsConfidentialityGuard::redact($smsMessage, $user);
        app(\App\Services\Sms\CatalogBreadcrumbResolver::class)->annotateMessages([$smsMessage]);

        return view('Admin.sms.logs.show', compact('smsMessage'));
    }
}
