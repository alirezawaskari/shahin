<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\Sms\InsufficientBalanceException;
use App\Exceptions\Sms\TierAccessDeniedException;
use App\Helpers\FilterHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Sms\PreviewBulkSmsRequest;
use App\Http\Requests\Admin\Sms\SendBulkSmsRequest;
use App\Models\Category;
use App\Models\GhorfeOnlineList;
use App\Models\Groupsms;
use App\Models\Province;
use App\Models\Role;
use App\Models\Setting;
use App\Models\SmsTemplate;
use App\Services\Sms\BulkSmsRecipientFilterService;
use App\Services\Sms\BulkSmsSendService;
use App\Services\Sms\SmsAccessTierService;
use App\Services\SmsSettings\SmsCmsContentService;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class SmsBulkSendController extends Controller
{
    public function __construct(
        protected BulkSmsSendService $bulkSmsSendService,
        protected BulkSmsRecipientFilterService $recipientFilterService,
        protected SmsAccessTierService $accessTierService,
        protected SmsCmsContentService $cmsContent,
        protected \App\Services\Sms\SingleSmsSendService $singleSmsSendService
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
     * «ارسال پیامک» of the requests page (flagged __direct by requestFilterSms.js) is for
     * holders of view-phone-numbers only; the same endpoints serve the bulk page, which is not.
     */
    protected function denyDirectSms(array $filters): ?JsonResponse
    {
        if (empty($filters['request_filter']['__direct']) || $this->accessTierService->canViewPhoneNumbers(auth()->user())) {
            return null;
        }

        return response()->json([
            'status' => 'error',
            'message' => 'شما مجوز ارسال پیامک مستقیم از نتیجه فیلتر را ندارید.',
        ], 403);
    }

    /**
     * The audience pool follows the sender's access tier (no manual choice): Diamond and above reach
     * the advertising contact bank, lower tiers their own business contacts.
     */
    protected function recipientTypeFor(?\App\Models\User $user): string
    {
        return $user && $this->accessTierService->canAccess($user, SmsAccessTierService::TIER_DIAMOND)
            ? 'advertising'
            : 'normal';
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
            $advertisingNumbers = $this->singleSmsSendService->advertisingSenderQuery($business)
                ->get()
                ->map(function ($sender) use ($business) {
                    $sender->current_balance = (float) ($business->advertising_balance ?? 0);
                    return $sender;
                });
        }

        if ($advertisingNumbers->isEmpty()) {
            $defaultSender = Setting::where('key', 'sms_sender_number')->first()
                ?? Setting::where('key', 'iranSms_linenumber')->first();
            if ($defaultSender) {
                $defaultSender->current_balance = (float) ($business?->advertising_balance ?? 0);
                $advertisingNumbers = collect([$defaultSender]);
            }
        }

        // Active SMS templates (scoped to business, OTP included only if user tier has OTP access)
        $templates = SmsTemplate::active()
            ->when(!$this->accessTierService->canUseOtp(auth()->user()),
                fn($q) => $q->notOtp())
            ->when($business, fn($q) => $q->where('ghorfe_online_list_id', $business->id))
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
            $recentCampaigns = Groupsms::campaigns()
                ->where('ghorfe_online_list_id', $business->id)
                ->where('status', '!=', Groupsms::STATUS_PENDING_QUEUE)
                ->with(['senderNumber', 'template'])
                ->latest()
                ->take(10)
                ->get();
        }

        // Bottom tabs: message log, outgoing queue, campaigns (no separate pages any more).
        $activeTab = in_array($request->query('tab'), ['logs', 'queue', 'campaigns'], true) ? $request->query('tab') : 'logs';

        $recentMessages = Groupsms::messages()
            ->where('ghorfe_online_list_id', $business->id)
            ->with(['senderNumber', 'user'])
            ->latest()
            ->take(15)
            ->get();
        \App\Services\Sms\SmsConfidentialityGuard::redactMany($recentMessages, $user);

        $queuedList = Groupsms::pendingQueue()
            ->where('ghorfe_online_list_id', $business->id)
            ->with(['senderNumber', 'template', 'creator'])
            ->latest()
            ->paginate(10, ['*'], 'queue_page')
            ->appends(['tab' => 'queue']);

        $campaignQuery = Groupsms::campaigns()
            ->where('type', 'bulk')
            ->where('status', '!=', Groupsms::STATUS_PENDING_QUEUE)
            ->where('ghorfe_online_list_id', $business->id)
            ->with(['senderNumber', 'template', 'creator'])
            ->latest();
        if ($request->filled('status') && $request->query('status') !== 'all') {
            $campaignQuery->where('status', $request->query('status'));
        }
        $campaignList = $campaignQuery->paginate(10, ['*'], 'campaigns_page')
            ->appends(['tab' => 'campaigns', 'status' => $request->query('status')]);

        $userTier = $this->accessTierService->effectiveTier($user);

        // OTP panel (Titanium only): fixed system line + the business' OTP balance.
        $canUseOtp = $this->accessTierService->canUseOtp($user);
        $otpSender = null;
        $otpBalance = 0.0;
        if ($canUseOtp) {
            $otpSender = Setting::query()->where('type', 'otp')->where('status', 'active')->first()
                ?? Setting::where('key', 'sms_sender_number')->first()
                ?? Setting::where('key', 'iranSms_linenumber')->first();
            $otpBalance = (float) ($business->otp_balance ?? 0);
        }

        $regulatoryRulesText = $this->cmsContent->regulatoryRulesText();
        // Promotional subject categories only (event/greeting, sales, support)
        $dbGroups = Setting::where('key', 'sms_message_group')
            ->when($business, fn($q) => $q->where('settingable_id', $business->id))
            ->get()
            ->filter(fn($s) => empty($s->parent_id))
            ->filter(fn($s) => $s->isPromotional())
            ->values();

        $groups = $dbGroups->isNotEmpty() ? $dbGroups : collect([
            new Setting(['id' => 1, 'key' => 'sms_message_group', 'value' => json_encode(['slug' => 'monasebati', 'name' => 'مناسبتی و تبریک'], JSON_UNESCAPED_UNICODE)]),
            new Setting(['id' => 2, 'key' => 'sms_message_group', 'value' => json_encode(['slug' => 'foroush', 'name' => 'فروش و بازاریابی'], JSON_UNESCAPED_UNICODE)]),
            new Setting(['id' => 3, 'key' => 'sms_message_group', 'value' => json_encode(['slug' => 'poshtibani', 'name' => 'پشتیبانی مشتریان'], JSON_UNESCAPED_UNICODE)]),
        ]);

        // Resuming an entry of the outgoing queue: prefill the compose page from it.
        $queuedCampaign = null;
        $queuedInput = null;
        if ($request->filled('queue')) {
            $queuedCampaign = $this->loadQueuedCampaign((int) $request->input('queue'), $business);
            $queuedInput = $this->bulkSmsSendService->queuedCampaignToInput($queuedCampaign);
            // datetime-local format, so the picker can be refilled as-is
            $queuedInput['scheduled_at'] = $queuedInput['scheduled_at']?->format('Y-m-d\TH:i');
        }
        $queuedCount = Groupsms::pendingQueue()->where('ghorfe_online_list_id', $business->id)->count();
        $costPerPage = (float) config('services.sms.cost_per_page', 1200);

        return view('Admin.sms.bulk.index', compact(
            'queuedCampaign',
            'queuedInput',
            'queuedCount',
            'costPerPage',
            'business',
            'user',
            'userTier',
            'activeTab',
            'recentMessages',
            'queuedList',
            'campaignList',
            'canUseOtp',
            'otpSender',
            'otpBalance',
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

        $recipientType = $this->recipientTypeFor($user);
        $serviceType = $this->bulkSmsSendService->resolveServiceType($request->all());
        $filters = (array) $request->input('filters', []);

        if ($serviceType === 'otp' && !$this->accessTierService->canUseOtp($user)) {
            return response()->json([
                'status' => 'error',
                'error_type' => 'tier_denied',
                'message' => 'دسترسی به پنل OTP برای سطح دسترسی شما مجاز نیست.',
            ], 403);
        }

        if ($denied = $this->denyDirectSms($filters)) {
            return $denied;
        }

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
            $template = SmsTemplate::find($templateId);
            $rawMessage = $template?->content ?? '';
        }

        $representativeUser = $this->bulkSmsSendService->resolveWorstCaseRecipient($filterResult['valid_recipients']);
        $resolvedForEstimate = $this->bulkSmsSendService->resolvePlaceholders($rawMessage, [], $representativeUser);
        $segmentation = $this->bulkSmsSendService->calculateMessageSegmentation($resolvedForEstimate, $serviceType);
        $pageCount = $segmentation['page_count'];
        $tariff = $segmentation['tariff'];

        $validCount = $filterResult['valid_count'];

        // 4. Resolve sender number & balance
        $senderNumberId = $request->input('sender_number_id');
        $currentBalance = 0.0;
        $senderNumber = null;

        if ($business && ($senderNumberId || $serviceType === 'otp')) {
            try {
                $senderNumber = $this->bulkSmsSendService->resolveSenderNumberFor($serviceType, $business, $senderNumberId ? (int) $senderNumberId : null, $user);
                $balanceInfo = $this->bulkSmsSendService->getBalance($business, $serviceType);
                $currentBalance = $balanceInfo['balance'];
            } catch (\Throwable) {
                // Invalid or unassigned sender number
            }
        }

        // «کاهش تعداد مخاطبین»: what the balance can pay for, and the (capped) audience actually priced.
        $maxAffordable = $this->bulkSmsSendService->maxAffordableRecipients($currentBalance, $pageCount);
        $effectiveCount = $validCount;
        if ((int) $request->input('max_recipients') > 0) {
            $effectiveCount = min($validCount, (int) $request->input('max_recipients'), $maxAffordable);
        }
        $totalEstimatedCost = $effectiveCount * $segmentation['cost_per_message'];

        $hasSufficientBalance = ($totalEstimatedCost > 0) ? ($currentBalance >= $totalEstimatedCost) : true;
        $balanceDeficit = ($totalEstimatedCost > $currentBalance) ? ($totalEstimatedCost - $currentBalance) : 0;
        $isLowBalance = ($currentBalance < 100000) || ($balanceDeficit > 0);

        // Phone numbers (even masked) are shown only to holders of view-phone-numbers; everyone
        // else gets the aggregated counts and no samples at all.
        $recipientSummary = [
            'total_candidates' => $filterResult['total_candidates'],
            'valid_count' => $filterResult['valid_count'],
            'invalid_count' => $filterResult['invalid_count'],
            'duplicate_count' => $filterResult['duplicate_count'],
        ];
        if ($this->accessTierService->canViewPhoneNumbers($user)) {
            $recipientSummary['invalid_sample'] = array_slice($filterResult['invalid_recipients'], 0, 5);
            $recipientSummary['valid_sample'] = array_slice($filterResult['valid_recipients'], 0, 5);
        }

        return response()->json([
            'status' => 'success',
            'recipient_summary' => $recipientSummary,
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
                'max_affordable_recipients' => $maxAffordable,
                'effective_recipient_count' => $effectiveCount,
                'cost_per_page' => (float) config('services.sms.cost_per_page', 1200),
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

        if ($denied = $this->denyDirectSms((array) $request->input('filters', []))) {
            return $denied;
        }

        $validatedData = $request->validated();
        $validatedData['recipient_type'] = $this->recipientTypeFor($user);
        // `type` in a bulk form request is the OTP classification radio, never the campaign type.
        unset($validatedData['type']);
        if ($request->filled('custom_numbers')) {
            $filters = $validatedData['filters'] ?? [];
            $filters['custom_numbers'] = $request->input('custom_numbers');
            $validatedData['filters'] = $filters;
        }

        try {
            $campaign = $this->bulkSmsSendService->createCampaign($validatedData, $user, $business);

            // The send was resumed from the outgoing queue: that entry is now a real campaign.
            if (!empty($validatedData['queue_id'])) {
                Groupsms::pendingQueue()
                    ->where('ghorfe_online_list_id', $business->id)
                    ->whereKey($validatedData['queue_id'])
                    ->delete();
            }

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
                    'deficit' => max(0, $e->requiredAmount - $e->currentBalance),
                    'max_affordable_recipients' => $e->maxAffordableRecipients,
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
     * Persist unsent compose-page content as a pending_queue campaign (no debit, no send).
     * Called by the explicit «ذخیره در صف ارسال» button and by the page-leave beacon.
     */
    public function saveToQueue(Request $request): JsonResponse
    {
        $business = $this->resolveBusiness($request);
        $user = auth()->user();

        $validator = Validator::make($request->all(), [
            'recipient_type' => ['nullable', 'string', 'in:normal,advertising'],
            'campaign_name' => ['nullable', 'string', 'max:100'],
            'service_type' => ['nullable', 'string', 'in:advertising,otp'],
            'otp_category' => ['nullable', 'string', 'in:' . implode(',', \App\Enums\SmsOtpCategory::values())],
            'classification' => ['nullable', 'string', 'in:regular,classified'],
            'message' => ['nullable', 'string'],
            'sms_message_template_id' => ['nullable', 'integer', 'exists:sms_templates,id'],
            'sms_message_group_id' => ['nullable'],
            'sender_number_id' => ['nullable', 'integer', 'exists:ghorfe_settings,id'],
            'filters' => ['nullable', 'array'],
            'scheduled_at' => ['nullable', 'date'],
            'custom_numbers' => ['nullable', 'string'],
            'queue_id' => ['nullable', 'integer'],
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()->first()], 422);
        }

        $filters = (array) $request->input('filters', []);
        if ($denied = $this->denyDirectSms($filters)) {
            return $denied;
        }
        if ($request->filled('custom_numbers')) {
            $filters['custom_numbers'] = $request->input('custom_numbers');
        }

        $data = $request->only([
            'campaign_name', 'service_type', 'otp_category', 'classification', 'message', 'sms_message_template_id',
            'sms_message_group_id', 'sender_number_id', 'scheduled_at',
        ]);
        $data['recipient_type'] = $this->recipientTypeFor($user);
        $data['filters'] = $filters;

        try {
            $existing = $request->filled('queue_id')
                ? $this->loadQueuedCampaign((int) $request->input('queue_id'), $business)
                : null;

            $queued = $this->bulkSmsSendService->saveToQueue($data, $user, $business, $existing);
        } catch (TierAccessDeniedException $e) {
            return response()->json([
                'status' => 'error',
                'error_type' => 'tier_denied',
                'message' => $e->getMessage(),
            ], 403);
        } catch (DomainException $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'پیش‌نویس در صف ارسال ذخیره شد.',
            'queue_id' => $queued->id,
            'queue_url' => route('admin.sms.bulk.index', ['tab' => 'queue']),
        ]);
    }

    /**
     * Outgoing queue («صف ارسال»): the current business' unsent pending_queue campaigns.
     */
    public function queue(Request $request): RedirectResponse
    {
        return redirect()->route('admin.sms.bulk.index', ['tab' => 'queue']);
    }

    /**
     * «ارسال اکنون»: goes through the normal send path (tier gate + balance guard).
     */
    public function sendQueued(Request $request, Groupsms $campaign): RedirectResponse
    {
        $business = $this->resolveBusiness($request);
        $queued = $this->loadQueuedCampaign($campaign->id, $business);

        try {
            $sent = $this->bulkSmsSendService->sendQueuedCampaign(
                $queued,
                auth()->user(),
                $business,
                $request->filled('max_recipients') ? (int) $request->input('max_recipients') : null
            );
        } catch (InsufficientBalanceException $e) {
            return redirect()->route('admin.sms.bulk.index', ['tab' => 'queue'])->withErrors(['balance' => $e->getMessage()]);
        } catch (TierAccessDeniedException $e) {
            return redirect()->route('admin.sms.bulk.index', ['tab' => 'queue'])->withErrors(['tier' => $e->getMessage()]);
        } catch (DomainException $e) {
            return redirect()->route('admin.sms.bulk.index', ['tab' => 'queue'])->withErrors(['error' => $e->getMessage()]);
        }

        return redirect()->route('admin.sms.bulk.campaigns.show', $sent)
            ->with('success', 'کمپین پیامکی با موفقیت ثبت گردید.');
    }

    public function destroyQueued(Request $request, Groupsms $campaign): RedirectResponse
    {
        $business = $this->resolveBusiness($request);
        $this->loadQueuedCampaign($campaign->id, $business)->delete();

        return redirect()->route('admin.sms.bulk.index', ['tab' => 'queue'])->with('success', 'مورد از صف ارسال حذف شد.');
    }

    /**
     * Fetch a pending_queue campaign of THIS business and re-run the tier gate on its stored
     * audience filter (the user may have lost access since it was saved). 404 otherwise.
     */
    protected function loadQueuedCampaign(int $id, GhorfeOnlineList $business): Groupsms
    {
        $queued = Groupsms::pendingQueue()
            ->where('ghorfe_online_list_id', $business->id)
            ->whereKey($id)
            ->firstOrFail();

        $input = $this->bulkSmsSendService->queuedCampaignToInput($queued);
        try {
            $this->accessTierService->assertCanQuery(auth()->user(), $input['filters'], $input['recipient_type'], $business->id);
        } catch (TierAccessDeniedException $e) {
            abort(403, $e->getMessage());
        }

        return $queued;
    }

    /**
     * List all bulk campaigns.
     */
    public function campaigns(Request $request): RedirectResponse
    {
        return redirect()->route('admin.sms.bulk.index', array_filter(['tab' => 'campaigns', 'status' => $request->query('status')]));
    }

    /**
     * Live Campaign Status View.
     */
    public function statusView(Groupsms $campaign): View
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
    public function statusApi(Groupsms $campaign): JsonResponse
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
                    'pending_queue' => 'در صف ارسال',
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
    private function abortUnlessCampaignInBusiness(Groupsms $campaign): void
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
