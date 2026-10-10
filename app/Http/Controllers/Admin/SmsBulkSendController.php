<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\Sms\InsufficientBalanceException;
use App\Exceptions\Sms\TierAccessDeniedException;
use App\Helpers\FilterHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Sms\PreviewBulkSmsRequest;
use App\Http\Requests\Admin\Sms\SendBulkSmsRequest;
use App\Models\AdvertisingBalance;
use App\Models\Category;
use App\Models\Province;
use App\Models\Role;
use App\Models\SmsCampaign;
use App\Models\SmsMessageTemplate;
use App\Services\Sms\BulkSmsRecipientFilterService;
use App\Services\Sms\BulkSmsSendService;
use App\Services\Sms\SmsAccessTierService;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SmsBulkSendController extends Controller
{
    public function __construct(
        protected BulkSmsSendService $bulkSmsSendService,
        protected BulkSmsRecipientFilterService $recipientFilterService,
        protected SmsAccessTierService $accessTierService
    ) {
    }

    /**
     * Display the Bulk SMS Send Interface.
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

        // Active SMS templates
        $templates = SmsMessageTemplate::active()
            ->with('group')
            ->latest()
            ->get();

        // Filter helper options
        $roles = class_exists(Role::class) && \Illuminate\Support\Facades\Schema::hasTable('roles') ? Role::all() : collect();
        $businesses = FilterHelper::ghorfeFilterOptions();
        $categories = class_exists(Category::class) && \Illuminate\Support\Facades\Schema::hasTable('categories') ? Category::whereNull('parent_id')->orWhere('depth', 1)->get() : collect();
        $provinces = class_exists(Province::class) && \Illuminate\Support\Facades\Schema::hasTable('provinces') ? Province::all() : collect();

        // Recent campaigns
        $recentCampaigns = collect();
        if ($business) {
            $recentCampaigns = SmsCampaign::where('ghorfe_online_list_id', $business->id)
                ->with(['senderNumber', 'template'])
                ->latest()
                ->take(10)
                ->get();
        }

        $userTier = $user ? ($user->access_tier ?: 'silver') : 'silver';

        return view('Admin.sms.bulk.index', compact(
            'business',
            'user',
            'userTier',
            'advertisingNumbers',
            'templates',
            'roles',
            'businesses',
            'categories',
            'provinces',
            'recentCampaigns'
        ));
    }

    /**
     * Real-time recipient filtering, de-duplication, invalid flagging, and cost calculation preview.
     */
    public function preview(PreviewBulkSmsRequest $request): JsonResponse
    {
        $business = ghorfe() ?? abort(403, 'کسب‌وکار فعالی برای این درخواست انتخاب نشده است.');
        $user = auth()->user();

        $recipientType = (string) $request->input('recipient_type', 'normal');
        $filters = (array) $request->input('filters', []);

        // Include manual numbers if passed
        if ($request->filled('custom_numbers')) {
            $filters['custom_numbers'] = $request->input('custom_numbers');
        }

        // 1. Verify Tier Gate
        try {
            $this->accessTierService->assertCanQuery($user, $filters, $recipientType);
        } catch (TierAccessDeniedException $e) {
            return response()->json([
                'status' => 'error',
                'error_type' => 'tier_denied',
                'message' => $e->getMessage(),
                'required_tier' => $e->requiredTier,
                'user_tier' => $e->userTier,
            ], 403);
        }

        // 2. Filter, validate, and de-duplicate recipients
        $filterResult = $this->recipientFilterService->filterRecipients($filters, $user, $business, $recipientType);

        // 3. Message segmentation & Cost calculation
        $rawMessage = (string) $request->input('message', '');
        $templateId = $request->input('sms_message_template_id');
        if ($templateId && empty($rawMessage)) {
            $template = SmsMessageTemplate::find($templateId);
            $rawMessage = $template?->content ?? '';
        }

        $autoAppendCancel = filter_var($request->input('auto_append_cancel', true), FILTER_VALIDATE_BOOLEAN);
        $segmentation = $this->bulkSmsSendService->calculateMessageSegmentation($rawMessage, $autoAppendCancel);
        $pageCount = $segmentation['page_count'];
        $tariff = $segmentation['tariff'];

        $validCount = $filterResult['valid_count'];
        $totalEstimatedCost = $this->bulkSmsSendService->calculateCampaignCost($validCount, $pageCount);

        // 4. Resolve sender number & balance
        $senderNumberId = $request->input('sender_number_id');
        $currentBalance = 0.0;
        $senderNumber = null;

        if ($business && $senderNumberId) {
            try {
                $senderNumber = $this->bulkSmsSendService->resolveAdvertisingSenderNumber($business, (int) $senderNumberId);
                $balanceInfo = $this->bulkSmsSendService->getBalance($business, $senderNumber);
                $currentBalance = $balanceInfo['balance'];
            } catch (\Throwable) {
                // Invalid or unassigned sender number
            }
        }

        $hasSufficientBalance = ($currentBalance >= $totalEstimatedCost) && ($totalEstimatedCost > 0);

        return response()->json([
            'status' => 'success',
            'recipient_summary' => [
                'total_candidates' => $filterResult['total_candidates'],
                'valid_count' => $filterResult['valid_count'],
                'invalid_count' => $filterResult['invalid_count'],
                'duplicate_count' => $filterResult['duplicate_count'],
                'invalid_sample' => array_slice($filterResult['invalid_recipients'], 0, 5),
                'valid_sample' => array_slice($filterResult['valid_recipients'], 0, 5),
            ],
            'message_summary' => [
                'raw_text' => $rawMessage,
                'resolved_text' => $segmentation['text'],
                'character_count' => $segmentation['character_count'],
                'page_count' => $segmentation['page_count'],
                'is_unicode' => $segmentation['is_unicode'],
                'cost_per_message' => $segmentation['cost_per_message'],
                'tariff' => $tariff,
            ],
            'financial_summary' => [
                'estimated_total_cost' => $totalEstimatedCost,
                'current_balance' => $currentBalance,
                'balance_after' => max(0, $currentBalance - $totalEstimatedCost),
                'has_sufficient_balance' => $hasSufficientBalance,
                'balance_deficit' => max(0, $totalEstimatedCost - $currentBalance),
            ],
            'access_tier' => [
                'user_tier' => $user->access_tier ?: 'silver',
                'required_tier' => $filterResult['required_tier'],
                'is_allowed' => true,
            ],
        ]);
    }

    /**
     * Launch the bulk SMS campaign.
     */
    public function send(SendBulkSmsRequest $request): JsonResponse|RedirectResponse
    {
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

        $validatedData = $request->validated();
        if ($request->filled('custom_numbers')) {
            $filters = $validatedData['filters'] ?? [];
            $filters['custom_numbers'] = $request->input('custom_numbers');
            $validatedData['filters'] = $filters;
        }

        try {
            $campaign = $this->bulkSmsSendService->createCampaign($validatedData, $user, $business);

            if ($request->wantsJson()) {
                return response()->json([
                    'status' => 'success',
                    'message' => $campaign->status === 'scheduled'
                        ? 'کمپین پیامکی برای تاریخ مشخص شده زمان‌بندی شد.'
                        : 'کمپین پیامکی ایجاد و در صف ارسال قرار گرفت.',
                    'campaign_id' => $campaign->id,
                    'status_url' => route('admin.sms.bulk.campaigns.show', $campaign),
                    'campaign' => $campaign,
                ]);
            }

            return redirect()->route('admin.sms.bulk.campaigns.show', $campaign)
                ->with('success', 'کمپین پیامکی با موفقیت ثبت گردید.');
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
     * List all bulk campaigns.
     */
    public function campaigns(Request $request): View
    {
        $business = ghorfe() ?? abort(403, 'کسب‌وکار فعالی برای این درخواست انتخاب نشده است.');

        $query = SmsCampaign::query()
            ->where('type', 'bulk')
            ->with(['senderNumber', 'template', 'creator'])
            ->latest();

        if ($business) {
            $query->where('ghorfe_online_list_id', $business->id);
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $campaigns = $query->paginate(15);

        return view('Admin.sms.bulk.campaigns', compact('business', 'campaigns'));
    }

    /**
     * Live Campaign Status View.
     */
    public function statusView(SmsCampaign $campaign): View
    {
        $campaign->load(['senderNumber', 'template', 'creator', 'business']);

        $recipients = $campaign->recipients()
            ->with('user')
            ->latest()
            ->paginate(30);

        return view('Admin.sms.bulk.status', compact('campaign', 'recipients'));
    }

    /**
     * Live Campaign Status JSON endpoint for polling.
     */
    public function statusApi(SmsCampaign $campaign): JsonResponse
    {
        $campaign->refresh();

        $total = max(1, $campaign->total_recipients);
        $progress = round(($campaign->sent_count / $total) * 100, 1);
        $isFinished = in_array($campaign->status, ['completed', 'failed'], true);

        return response()->json([
            'status' => 'success',
            'campaign' => [
                'id' => $campaign->id,
                'status' => $campaign->status,
                'status_label' => match ($campaign->status) {
                    'draft' => 'پیش‌نویس',
                    'scheduled' => 'زمان‌بندی شده',
                    'sending' => 'در حال ارسال',
                    'completed' => 'تکمیل شده',
                    'failed' => 'ناموفق',
                    default => $campaign->status,
                },
                'total_recipients' => $campaign->total_recipients,
                'sent_count' => $campaign->sent_count,
                'success_count' => $campaign->success_count,
                'failed_count' => $campaign->failed_count,
                'pending_count' => $campaign->pending_count,
                'progress_percentage' => min(100, $progress),
                'cost_estimate' => (float) $campaign->cost_estimate,
                'scheduled_at' => $campaign->scheduled_at?->toDateTimeString(),
                'created_at' => $campaign->created_at?->toDateTimeString(),
                'is_finished' => $isFinished,
            ],
        ]);
    }
}
