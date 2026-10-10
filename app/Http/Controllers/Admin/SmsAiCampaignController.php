<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\Sms\InsufficientBalanceException;
use App\Exceptions\Sms\TierAccessDeniedException;
use App\Http\Controllers\Controller;
use App\Models\AdvertisingBalance;
use App\Models\SmsCampaign;
use App\Services\Sms\AiCampaignAssistant;
use App\Services\Sms\BulkSmsSendService;
use App\Services\Sms\SmsAccessTierService;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SmsAiCampaignController extends Controller
{
    public function __construct(
        protected AiCampaignAssistant $aiAssistant,
        protected BulkSmsSendService $bulkSmsSendService,
        protected SmsAccessTierService $accessTierService
    ) {
    }

    /**
     * Display the AI-Assisted Campaign Studio.
     */
    public function index(Request $request): View
    {
        $business = ghorfe() ?? abort(403, 'کسب‌وکار فعالی برای این درخواست انتخاب نشده است.');
        $user = auth()->user();

        // Advertising sender numbers assigned to this business with balances
        $advertisingNumbers = collect();
        if ($business) {
            $advertisingNumbers = $business->senderNumbers()
                ->where('sender_numbers.type', 'advertising')
                ->where('sender_numbers.status', 'active')
                ->get()
                ->map(function ($sender) use ($business) {
                    $balance = AdvertisingBalance::where('ghorfe_online_list_id', $business->id)
                        ->where('sender_number_id', $sender->id)
                        ->value('balance') ?? 0;
                    $sender->current_balance = (float) $balance;
                    return $sender;
                });
        }

        // Recent AI campaigns
        $recentCampaigns = collect();
        if ($business) {
            $recentCampaigns = SmsCampaign::where('ghorfe_online_list_id', $business->id)
                ->where('type', 'ai')
                ->with(['senderNumber', 'creator'])
                ->latest()
                ->take(10)
                ->get();
        }

        $userTier = $user ? ($user->access_tier ?: 'silver') : 'silver';
        $aiModel = $this->aiAssistant->resolveAiModel();

        return view('Admin.sms.ai.index', compact(
            'business',
            'user',
            'userTier',
            'advertisingNumbers',
            'recentCampaigns',
            'aiModel'
        ));
    }

    /**
     * Step 1 & 2: User enters natural language goal -> AI proposes target audience filters + count + rationale.
     */
    public function proposeAudience(Request $request): JsonResponse
    {
        $request->validate([
            'goal' => 'required|string|min:3|max:1000',
            'recipient_type' => 'nullable|string|in:normal,advertising',
        ], [
            'goal.required' => 'لطفاً هدف کمپین خود را به زبان طبیعی وارد نمایید.',
            'goal.min' => 'هدف کمپین باید حداقل شامل ۳ کاراکتر باشد.',
        ]);

        $business = ghorfe() ?? abort(403, 'کسب‌وکار فعالی برای این درخواست انتخاب نشده است.');
        $user = auth()->user();

        if (!$business) {
            return response()->json([
                'status' => 'error',
                'message' => 'کسب‌وکار معتبری برای اجرای کمپین یافت نشد.',
            ], 422);
        }

        try {
            $result = $this->aiAssistant->proposeAudience(
                $request->input('goal'),
                $user,
                $business,
                ['recipient_type' => $request->input('recipient_type', 'normal')]
            );

            return response()->json([
                'status' => 'success',
                'data' => $result,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Step 3: Explicit Human Approval Gate for Target Audience.
     */
    public function approveAudience(Request $request): JsonResponse
    {
        $request->validate([
            'goal' => 'required|string',
            'filters' => 'nullable|array',
            'approved' => 'required|boolean|accepted',
            'rationale' => 'nullable|string',
            'recipient_count' => 'nullable|integer',
        ], [
            'approved.accepted' => 'برای ادامه، تایید مخاطبان پیشنهادی توسط کاربر الزامی است.',
        ]);

        // Store approved audience in session to guarantee step order
        $approvedAudience = [
            'goal' => $request->input('goal'),
            'filters' => $request->input('filters'),
            'rationale' => $request->input('rationale', ''),
            'recipient_count' => (int) $request->input('recipient_count', 0),
            'approved_at' => now()->toIso8601String(),
            // Bound later, at calculateCost, once the user has fixed the final text and sender.
            'message' => null,
            'sender_number_id' => null,
        ];

        session()->put('sms_ai_approved_audience', $approvedAudience);

        return response()->json([
            'status' => 'success',
            'message' => 'جامعه مخاطبان با موفقیت تایید شد.',
            'approved_audience' => $approvedAudience,
        ]);
    }

    /**
     * Step 4: AI drafts message text sized to the audience and page-budget.
     */
    public function draftMessage(Request $request): JsonResponse
    {
        $request->validate([
            'goal' => 'required|string',
            'approved_audience' => 'required|array',
            'page_budget' => 'nullable|integer|min:1|max:4',
        ]);

        $business = ghorfe() ?? abort(403, 'کسب‌وکار فعالی برای این درخواست انتخاب نشده است.');
        $user = auth()->user();

        if (!$business) {
            return response()->json([
                'status' => 'error',
                'message' => 'کسب‌وکار معتبری یافت نشد.',
            ], 422);
        }

        $pageBudget = (int) $request->input('page_budget', 1);

        try {
            $result = $this->aiAssistant->draftMessage(
                $request->input('goal'),
                $request->input('approved_audience'),
                $pageBudget,
                $user,
                $business
            );

            return response()->json([
                'status' => 'success',
                'data' => $result,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Step 6: Final cost calculation (reusing bulk-send cost logic).
     */
    public function calculateCost(Request $request): JsonResponse
    {
        $request->validate([
            'message' => 'required|string',
            'filters' => 'nullable|array',
            'sender_number_id' => 'nullable|integer',
            'auto_append_cancel' => 'nullable|boolean',
            'recipient_type' => 'nullable|string|in:normal,advertising',
        ]);

        $business = ghorfe() ?? abort(403, 'کسب‌وکار فعالی برای این درخواست انتخاب نشده است.');
        $user = auth()->user();

        if (!$business) {
            return response()->json([
                'status' => 'error',
                'message' => 'کسب‌وکار معتبری یافت نشد.',
            ], 422);
        }

        try {
            $senderNumberId = $request->filled('sender_number_id') ? (int) $request->input('sender_number_id') : null;
            $autoAppendCancel = filter_var($request->input('auto_append_cancel', true), FILTER_VALIDATE_BOOLEAN);
            $recipientType = $request->input('recipient_type', 'normal');

            $result = $this->aiAssistant->calculateCost(
                $request->input('message'),
                $request->input('filters'),
                $business,
                $user,
                $senderNumberId,
                $autoAppendCancel,
                $recipientType
            );

            // Bind the exact text and sender the user is costing to the stored approval,
            // so confirmAndLaunch can reject any change made after this point.
            $approved = session()->get('sms_ai_approved_audience');
            if (is_array($approved)) {
                $approved['message'] = (string) $request->input('message');
                $approved['sender_number_id'] = $senderNumberId;
                session()->put('sms_ai_approved_audience', $approved);
            }

            return response()->json([
                'status' => 'success',
                'data' => $result,
            ]);
        } catch (TierAccessDeniedException $e) {
            return response()->json([
                'status' => 'error',
                'error_type' => 'tier_denied',
                'message' => $e->getMessage(),
                'required_tier' => $e->requiredTier,
                'user_tier' => $e->userTier,
            ], 403);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Step 7 & 8: Final Human Confirmation -> Handoff to sms_campaigns row (type='ai', ai_goal_prompt populated)
     * Reusing BulkSmsSendService's actual send pipeline.
     */
    public function confirmAndLaunch(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate([
            'human_confirmed' => 'required|accepted',
            'goal' => 'required|string',
            'message' => 'required|string',
            'filters' => 'nullable|array',
            'sender_number_id' => 'nullable|integer',
            'auto_append_cancel' => 'nullable|boolean',
            'recipient_type' => 'nullable|string|in:normal,advertising',
            'scheduled_at' => 'nullable|date',
        ], [
            'human_confirmed.accepted' => 'تایید نهایی کاربر انسانی برای صدور دستور ارسال الزامی است و هوش مصنوعی بدون تایید اجازه ارسال ندارد.',
        ]);

        $business = ghorfe() ?? abort(403, 'کسب‌وکار فعالی برای این درخواست انتخاب نشده است.');
        $user = auth()->user();

        if (!$business) {
            if ($request->wantsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'کسب‌وکار معتبری برای ارسال پیامک یافت نشد.',
                ], 422);
            }
            return redirect()->back()->withErrors(['business' => 'کسب‌وکار معتبری برای ارسال پیامک یافت نشد.']);
        }

        // Hard Code-Level Human Confirmation Gate Check
        if (!$request->boolean('human_confirmed')) {
            if ($request->wantsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'تایید نهایی کاربر انسانی الزامی است.',
                ], 422);
            }
            return redirect()->back()->withErrors(['human_confirmed' => 'تایید نهایی کاربر انسانی الزامی است.']);
        }

        // The launch must match the audience the user approved in approveAudience().
        // Without this, a single approval could be replayed with different filters.
        $approved = session()->get('sms_ai_approved_audience');
        $launchGoal = (string) $request->input('goal');
        $launchFilters = (array) $request->input('filters', []);

        if (!is_array($approved)) {
            return $this->approvalMismatch($request, 'ابتدا باید مخاطبان پیشنهادی توسط کاربر تایید شوند.');
        }

        $launchSenderNumberId = $request->filled('sender_number_id') ? (int) $request->input('sender_number_id') : null;

        $approvedFingerprint = $this->approvalFingerprint(
            (string) ($approved['goal'] ?? ''),
            (array) ($approved['filters'] ?? []),
            isset($approved['message']) ? (string) $approved['message'] : null,
            isset($approved['sender_number_id']) ? (int) $approved['sender_number_id'] : null
        );
        $launchFingerprint = $this->approvalFingerprint(
            $launchGoal,
            $launchFilters,
            (string) $request->input('message'),
            $launchSenderNumberId
        );

        if (!hash_equals($approvedFingerprint, $launchFingerprint)) {
            // Invalidate the approval so it cannot be retried against altered payloads.
            session()->forget('sms_ai_approved_audience');

            return $this->approvalMismatch($request, 'مخاطبان ارسالی با مخاطبان تایید شده مطابقت ندارد. لطفا دوباره مخاطبان را تایید کنید.');
        }

        $campaignData = [
            'type' => 'ai',
            'ai_goal_prompt' => $request->input('goal'),
            'human_confirmed' => true,
            'message' => $request->input('message'),
            'filters' => (array) $request->input('filters', []),
            'sender_number_id' => $request->input('sender_number_id'),
            'auto_append_cancel' => filter_var($request->input('auto_append_cancel', true), FILTER_VALIDATE_BOOLEAN),
            'recipient_type' => $request->input('recipient_type', 'normal'),
            'scheduled_at' => $request->input('scheduled_at'),
        ];

        try {
            $campaign = $this->bulkSmsSendService->createCampaign($campaignData, $user, $business);

            // Clear session state
            session()->forget('sms_ai_approved_audience');

            if ($request->wantsJson()) {
                return response()->json([
                    'status' => 'success',
                    'message' => $campaign->status === 'scheduled'
                        ? 'کمپین هوش مصنوعی با موفقیت برای تاریخ تعیین شده زمان‌بندی شد.'
                        : 'کمپین هوش مصنوعی با موفقیت تایید و در صف ارسال قرار گرفت.',
                    'campaign_id' => $campaign->id,
                    'status_url' => route('admin.sms.ai.campaigns.show', $campaign),
                    'campaign' => $campaign,
                ]);
            }

            return redirect()->route('admin.sms.ai.campaigns.show', $campaign)
                ->with('success', 'کمپین هوش مصنوعی با موفقیت ثبت شد.');
        } catch (InsufficientBalanceException $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'status' => 'error',
                    'error_type' => 'insufficient_balance',
                    'message' => $e->getMessage(),
                    'current_balance' => $e->currentBalance,
                    'required_amount' => $e->requiredAmount,
                ], 422);
            }
            return redirect()->back()->withInput()->withErrors(['balance' => $e->getMessage()]);
        } catch (TierAccessDeniedException $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'status' => 'error',
                    'error_type' => 'tier_denied',
                    'message' => $e->getMessage(),
                    'required_tier' => $e->requiredTier,
                    'user_tier' => $e->userTier,
                ], 403);
            }
            return redirect()->back()->withInput()->withErrors(['tier' => $e->getMessage()]);
        } catch (DomainException $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $e->getMessage(),
                ], 422);
            }
            return redirect()->back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Stable hash of what the user approved: audience (goal + filters), the final
     * message text and the sender number. Filters are key-order independent.
     * A null message or sender means that part has not been bound yet.
     */
    private function approvalFingerprint(string $goal, array $filters, ?string $message, ?int $senderNumberId): string
    {
        return hash('sha256', json_encode([
            'goal' => $goal,
            'filters' => $this->sortKeysRecursive($filters),
            'message' => $message,
            'sender_number_id' => $senderNumberId,
        ], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
    }

    private function sortKeysRecursive(array $value): array
    {
        ksort($value);

        foreach ($value as $key => $item) {
            if (is_array($item)) {
                $value[$key] = $this->sortKeysRecursive($item);
            }
        }

        return $value;
    }

    private function approvalMismatch(Request $request, string $message): JsonResponse|RedirectResponse
    {
        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'error',
                'error_type' => 'approval_mismatch',
                'message' => $message,
            ], 422);
        }

        return redirect()->back()->withInput()->withErrors(['approval' => $message]);
    }

    /**
     * List all AI campaigns.
     */
    public function campaigns(Request $request): View
    {
        $business = ghorfe() ?? abort(403, 'کسب‌وکار فعالی برای این درخواست انتخاب نشده است.');

        $query = SmsCampaign::query()
            ->where('type', 'ai')
            ->with(['senderNumber', 'template', 'creator'])
            ->latest();

        if ($business) {
            $query->where('ghorfe_online_list_id', $business->id);
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $campaigns = $query->paginate(15);

        return view('Admin.sms.ai.campaigns', compact('business', 'campaigns'));
    }

    /**
     * View live status of an AI Campaign.
     */
    public function statusView(SmsCampaign $campaign): View
    {
        $this->abortUnlessCampaignInBusiness($campaign);

        $campaign->load(['senderNumber', 'template', 'creator', 'business']);

        $recipients = $campaign->recipients()
            ->with('user')
            ->latest()
            ->paginate(30);

        return view('Admin.sms.ai.status', compact('campaign', 'recipients'));
    }

    /**
     * Ensure the campaign belongs to the current business (superAdmin bypasses).
     * Fails closed when no business is active.
     */
    private function abortUnlessCampaignInBusiness(SmsCampaign $campaign): void
    {
        $business = ghorfe();

        if (auth()->user()?->isSuperAdmin()) {
            return;
        }

        if (!$business || (int) $campaign->ghorfe_online_list_id !== (int) $business->id) {
            abort(403, 'شما دسترسی به این کمپین را ندارید.');
        }
    }
}
