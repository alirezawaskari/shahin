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
use App\Models\GhorfeOnlineList;
use App\Models\Province;
use App\Models\Role;
use App\Models\SmsCampaign;
use App\Models\SmsMessageGroup;
use App\Models\SmsMessageTemplate;
use App\Services\Sms\BulkSmsRecipientFilterService;
use App\Services\Sms\BulkSmsSendService;
use App\Services\Sms\SmsAccessTierService;
use App\Services\SmsSettings\SmsCmsContentService;
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
        protected SmsAccessTierService $accessTierService,
        protected SmsCmsContentService $cmsContent
    ) {
    }

    /**
     * Resolve the current business context with fallback for SuperAdmin.
     */
    protected function resolveBusiness(Request $request): GhorfeOnlineList
    {
        $business = ghorfe();

        if (!$business && (auth()->user()?->isSuperAdmin() || app()->environment('testing'))) {
            $targetBusinessId = $request->input('business_id') ?: $request->input('business');
            $business = $targetBusinessId ? GhorfeOnlineList::find($targetBusinessId) : GhorfeOnlineList::first();
        }

        if (!$business) {
            abort(403, 'کسب‌وکار فعالی برای این درخواست انتخاب نشده است.');
        }

        return $business;
    }

    /**
     * Display the Bulk SMS Send Interface.
     */
    public function index(Request $request): View
    {
        $business = $this->resolveBusiness($request);
        $user = auth()->user();

        // Advertising sender numbers assigned to this business with balances
        $advertisingNumbers = collect();
        if ($business) {
            $advertisingNumbers = $business->senderNumbers()
                ->where('sender_numbers.type', 'advertising')
                ->where('sender_numbers.status', 'active')
                ->get()
                ->map(function ($sender) use ($business) {
                    $balance = AdvertisingBalance::where('ghorfe_online_list_id', $business->id)->value('balance') ?? 0;
                    $sender->current_balance = (float) $balance;
                    return $sender;
                });
        }

        // Active SMS templates (scoped to business)
        $templates = SmsMessageTemplate::active()
            ->when($business, fn($q) => $q->where('ghorfe_online_list_id', $business->id))
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

        $userTier = $this->accessTierService->effectiveTier($user);
        $regulatoryRulesText = $this->cmsContent->regulatoryRulesText();
        $groups = SmsMessageGroup::whereNull('parent_id')
            ->when($business, fn($q) => $q->where('ghorfe_online_list_id', $business->id))
            ->with(['subgroups' => function ($q) use ($business) {
                $q->when($business, fn($sq) => $sq->where('ghorfe_online_list_id', $business->id));
            }])
            ->orderBy('name')
            ->get();

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
            'recentCampaigns',
            'regulatoryRulesText',
            'groups'
        ));
    }

    /**
     * Real-time recipient filtering, de-duplication, invalid flagging, and cost calculation preview.
     */
    public function preview(PreviewBulkSmsRequest $request): JsonResponse
    {
        $business = $this->resolveBusiness($request);
        $user = auth()->user();

        $recipientType = (string) $request->input('recipient_type', 'normal');
        $filters = (array) $request->input('filters', []);

        // Include manual numbers if passed
        if ($request->filled('custom_numbers')) {
            $filters['custom_numbers'] = $request->input('custom_numbers');
        }

        // 1. Verify Tier Gate
        try {
            $this->accessTierService->assertCanQuery($user, $filters, $recipientType, $business->id);
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

        // 3. Message segmentation & Cost calculation. $rawMessage may still
        // contain unresolved placeholder tokens such as "{نام کاربر}".
        // Resolving it individually for every filtered recipient isn't
        // feasible for a cheap live preview, so — matching createCampaign()
        // — it is resolved against the recipient with the longest
        // name+family among the filtered set (a worst-case stand-in),
        // THEN laid through the same resolve → append لغو 11 → segment
        // order used everywhere else, so this preview's page_count doesn't
        // under-estimate what the actual send will charge.
        $rawMessage = (string) $request->input('message', '');
        $templateId = $request->input('sms_message_template_id');
        if ($templateId && empty($rawMessage)) {
            $template = SmsMessageTemplate::find($templateId);
            $rawMessage = $template?->content ?? '';
        }

        $representativeUser = $this->bulkSmsSendService->resolveWorstCaseRecipient($filterResult['valid_recipients']);
        $resolvedForEstimate = $this->bulkSmsSendService->resolvePlaceholders($rawMessage, [], $representativeUser);
        $segmentation = $this->bulkSmsSendService->calculateMessageSegmentation($resolvedForEstimate);
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
                $balanceInfo = $this->bulkSmsSendService->getBalance($business);
                $currentBalance = $balanceInfo['balance'];
            } catch (\Throwable) {
                // Invalid or unassigned sender number
            }
        }

        $totalEstimatedCost = $filterResult['valid_count'] * $segmentation['cost_per_message'];

        $hasSufficientBalance = ($totalEstimatedCost > 0) ? ($currentBalance >= $totalEstimatedCost) : true;
        $balanceDeficit = ($totalEstimatedCost > $currentBalance) ? ($totalEstimatedCost - $currentBalance) : 0;
        $isLowBalance = ($currentBalance < 100000) || ($balanceDeficit > 0);

        $canViewRawPhones = $user && ($user->isSuperAdmin() || $user->can('export-callcenter'));
        $maskedValidSample = array_map(function ($rec) use ($canViewRawPhones) {
            if (!$canViewRawPhones && !empty($rec['phone'])) {
                $p = (string) $rec['phone'];
                $rec['phone'] = strlen($p) >= 7 ? substr($p, 0, 4) . '***' . substr($p, -3) : '***';
            }
            return $rec;
        }, array_slice($filterResult['valid_recipients'], 0, 5));

        $maskedInvalidSample = array_map(function ($rec) use ($canViewRawPhones) {
            if (!$canViewRawPhones && !empty($rec['phone']) && $rec['phone'] !== '(خالی)') {
                $p = (string) $rec['phone'];
                $rec['phone'] = strlen($p) >= 7 ? substr($p, 0, 4) . '***' . substr($p, -3) : '***';
            }
            return $rec;
        }, array_slice($filterResult['invalid_recipients'], 0, 5));

        return response()->json([
            'status' => 'success',
            'recipient_summary' => [
                'total_candidates' => $filterResult['total_candidates'],
                'valid_count' => $filterResult['valid_count'],
                'invalid_count' => $filterResult['invalid_count'],
                'duplicate_count' => $filterResult['duplicate_count'],
                'invalid_sample' => $maskedInvalidSample,
                'valid_sample' => $maskedValidSample,
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
                'balance_deficit' => $balanceDeficit,
                'is_low_balance' => $isLowBalance,
                'sender_number' => $senderNumber ? $senderNumber->number : null,
                'sender_number_id' => $senderNumber ? $senderNumber->id : null,
                'business_id' => $business->id,
                'business_name' => $business->title,
            ],
            'access_tier' => [
                'user_tier' => $this->accessTierService->effectiveTier($user),
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
        $business = $this->resolveBusiness($request);
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
     * Read phone numbers out of an uploaded Excel / CSV file. Nothing is stored and nothing is
     * sent: the numbers are returned to the page, which drops them into the "custom numbers"
     * box so they go through the same validation, de-duplication, tier and cost pipeline as
     * typed numbers.
     */
    public function parseExcel(Request $request): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv,txt', 'max:2048'],
        ], [
            'file.required' => 'فایل اکسل را انتخاب کنید.',
            'file.mimes' => 'فقط فایل‌های xlsx، xls و csv پذیرفته می‌شوند.',
            'file.max' => 'حجم فایل نباید بیشتر از ۲ مگابایت باشد.',
        ]);

        try {
            $sheets = \Maatwebsite\Excel\Facades\Excel::toArray(new \App\Imports\RawSheetImport(), $request->file('file'));
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'خواندن فایل ممکن نشد. از سالم بودن فایل (xlsx / xls / csv) مطمئن شوید.',
            ], 422);
        }

        $result = $this->recipientFilterService->extractPhonesFromSheets($sheets);

        if ($result['valid_count'] === 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'هیچ شماره موبایل معتبری در فایل پیدا نشد.',
                'invalid_count' => $result['invalid_count'],
                'invalid_sample' => $result['invalid_sample'],
            ], 422);
        }

        return response()->json(['status' => 'success'] + $result);
    }

    /**
     * List all bulk campaigns.
     */
    public function campaigns(Request $request): View
    {
        $business = $this->resolveBusiness($request);

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
        $this->abortUnlessCampaignInBusiness($campaign);

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
        $this->abortUnlessCampaignInBusiness($campaign);

        $campaign->refresh();

        $total = max(1, $campaign->total_recipients);
        $progress = round(($campaign->sent_count / $total) * 100, 1);
        $isFinished = in_array($campaign->status, ['completed', 'completed_with_errors', 'failed'], true);

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
                    'completed_with_errors' => 'تکمیل ناقص (توقف به دلیل موجودی)',
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
