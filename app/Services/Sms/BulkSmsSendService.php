<?php

namespace App\Services\Sms;

use App\Classes\External\IranSms;
use App\Classes\External\NeginSms;
use App\Exceptions\Sms\InsufficientBalanceException;
use App\Jobs\SendBulkSmsCampaignJob;
use App\Models\GhorfeOnlineList;
use App\Models\Groupsms;
use App\Models\Setting;
use App\Models\SmsLog;
use App\Models\SmsTemplate;
use App\Models\User;
use Carbon\Carbon;
use DomainException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class BulkSmsSendService
{
    /**
     * Appended text for mandatory advertising opt-out in Iran telecom.
     */
    public const CANCEL_PHRASE = 'لغو 11';

    /**
     * Custom gateway dispatcher for testing or external mocks.
     *
     * @var callable|null
     */
    protected $gatewayDispatcher = null;

    /**
     * Global static gateway dispatcher for testing across queued jobs.
     *
     * @var callable|null
     */
    protected static $globalGatewayDispatcher = null;

    public function __construct(
        protected SingleSmsSendService $singleSmsSendService,
        protected BulkSmsRecipientFilterService $recipientFilterService,
        protected SmsAccessTierService $accessTierService,
        protected ?SmsAuditService $audit = null
    ) {
        $this->audit ??= new SmsAuditService();
    }

    /**
     * Set a custom gateway dispatcher callback (useful for testing).
     */
    public function setGatewayDispatcher(?callable $dispatcher): self
    {
        $this->gatewayDispatcher = $dispatcher;
        return $this;
    }

    /**
     * Set a global gateway dispatcher callback across all instances and queued jobs.
     */
    public static function setGlobalGatewayDispatcher(?callable $dispatcher): void
    {
        self::$globalGatewayDispatcher = $dispatcher;
    }

    /**
     * Bulk sends run on one of two panels: the promotional panel (default) or the OTP panel.
     *
     * @param array<string, mixed> $data
     */
    public function resolveServiceType(array $data): string
    {
        return ($data['service_type'] ?? 'advertising') === 'otp' ? 'otp' : 'advertising';
    }

    /**
     * Sender line of a bulk send. OTP always uses the single system OTP line (Titanium only);
     * the promotional panel uses one of the business' own advertising lines.
     */
    public function resolveSenderNumberFor(string $serviceType, GhorfeOnlineList $business, ?int $requestedSenderNumberId, User $actor): Setting
    {
        if ($serviceType === 'otp') {
            return $this->singleSmsSendService->resolveSenderNumber('otp', $business, null, $actor);
        }

        return $this->resolveAdvertisingSenderNumber($business, $requestedSenderNumberId);
    }

    /**
     * Resolve and validate advertising sender number for a given business.
     */
    public function resolveAdvertisingSenderNumber(GhorfeOnlineList $business, ?int $requestedSenderNumberId = null): Setting
    {
        $query = $business->senderNumbers()
            ->where('type', 'advertising')
            ->where('status', 'active');

        if ($requestedSenderNumberId) {
            $sender = (clone $query)->where('id', $requestedSenderNumberId)->first();
            if (!$sender) {
                throw new DomainException('شماره فرستنده انتخابی به این کسب‌وکار تخصیص نیافته است یا فعال نمی‌باشد.');
            }
            return $sender;
        }

        $sender = $query->first();
        if (!$sender) {
            throw new DomainException('هیچ شماره ارسال پیامک تبلیغاتی به این کسب‌وکار تخصیص نیافته است.');
        }

        return $sender;
    }

    /**
     * Get the business' advertising panel balance (shared by all its advertising lines).
     *
     * @return array{balance: float, model: GhorfeOnlineList, credit_type: string}
     */
    public function getBalance(GhorfeOnlineList $business, string $serviceType = 'advertising'): array
    {
        $column = $serviceType === 'otp' ? 'otp_balance' : 'advertising_balance';

        $balance = \Illuminate\Support\Facades\Schema::hasColumn('ghorfe_online_lists', $column)
            ? GhorfeOnlineList::whereKey($business->id)->value($column)
            : null;
        if ($balance !== null) {
            $business->{$column} = (float) $balance;
        }

        return [
            'balance' => (float) ($business->{$column} ?? 0),
            'model' => $business,
            'credit_type' => $serviceType === 'otp' ? 'otp' : 'advertising',
        ];
    }

    /**
     * Calculate cost per single message based on content segmentation and tariff.
     * Note: App\Models\Tariff and ServiceTariff are healthcare/clinic service tariffs.
     * SMS pricing uses config('services.sms.cost_per_page', 1200) as established in SingleSmsSendService.
     *
     * @return array{
     *     text: string,
     *     character_count: int,
     *     page_count: int,
     *     cost_per_message: float,
     *     is_unicode: bool,
     *     tariff: float
     * }
     */
    public function calculateMessageSegmentation(string $content, string $serviceType = 'advertising'): array
    {
        // Only the promotional panel gets the mandatory «لغو 11» suffix.
        $segmentation = $this->singleSmsSendService->calculateSegmentation($content, $serviceType === 'otp' ? 'otp' : 'advertising');
        $tariff = (float) config('services.sms.cost_per_page', 1200);
        $costPerMessage = (float) ($segmentation['page_count'] * $tariff);

        return [
            'text' => $segmentation['text'],
            'character_count' => $segmentation['character_count'],
            'page_count' => $segmentation['page_count'],
            'cost_per_message' => $costPerMessage,
            'is_unicode' => $segmentation['is_unicode'],
            'tariff' => $tariff,
        ];
    }

    /**
     * Calculate total campaign estimated cost: recipients × pages × tariff.
     */
    public function calculateCampaignCost(int $recipientCount, int $pageCount): float
    {
        $tariff = (float) config('services.sms.cost_per_page', 1200);
        return (float) ($recipientCount * $pageCount * $tariff);
    }

    /**
     * Pick a stand-in recipient for a pre-send cost/segmentation estimate.
     *
     * A true per-recipient preview would require resolving every recipient's
     * placeholders (e.g. "{نام کاربر}") before the campaign/recipient rows
     * exist, which isn't feasible for a cheap preview over a large filtered
     * set. Instead this resolves against the recipient with the longest
     * name+family among the filtered set, a worst-case stand-in that avoids
     * under-estimating page_count (and therefore the balance required)
     * relative to what executeCampaign() will actually charge per recipient.
     *
     * @param array<int, array{phone: string, user_id: int|null}> $validRecipients
     */
    public function resolveWorstCaseRecipient(array $validRecipients): ?User
    {
        $userIds = array_filter(array_unique(array_column($validRecipients, 'user_id')));
        if (empty($userIds)) {
            return null;
        }

        return User::query()
            ->whereIn('id', $userIds)
            ->get(['id', 'name', 'family'])
            ->sortByDesc(fn (User $user) => mb_strlen(($user->name ?? '') . ($user->family ?? ''), 'UTF-8'))
            ->first();
    }

    /**
     * Thin passthrough to SingleSmsSendService::resolvePlaceholders(), so
     * callers (e.g. the bulk preview endpoint) don't need their own
     * SingleSmsSendService dependency just to resolve a stand-in recipient's
     * placeholders before estimating segmentation.
     */
    public function resolvePlaceholders(string $rawContent, array $context = [], ?User $recipient = null): string
    {
        return $this->singleSmsSendService->resolvePlaceholders($rawContent, $context, $recipient);
    }

    /**
     * How many recipients a balance can pay for: floor(balance / (pages × cost_per_page)).
     */
    public function maxAffordableRecipients(float $balance, int $pageCount): int
    {
        $costPerMessage = max(1, $pageCount) * (float) config('services.sms.cost_per_page', 1200);

        return $costPerMessage > 0 ? (int) floor(max(0.0, $balance) / $costPerMessage) : 0;
    }

    /**
     * Check balance sufficiency and throw InsufficientBalanceException if insufficient.
     * $pageCount (when known) lets the exception carry the max affordable recipient count.
     *
     * @throws InsufficientBalanceException
     */
    public function verifyBalance(GhorfeOnlineList $business, float $requiredCost, ?int $pageCount = null, string $serviceType = 'advertising'): array
    {
        $balanceInfo = $this->getBalance($business, $serviceType);

        if ($balanceInfo['balance'] < $requiredCost) {
            throw new InsufficientBalanceException(
                sprintf(
                    'موجودی %s برای این کمپین کافی نیست. موجودی فعلی: %s ریال، هزینه مورد نیاز: %s ریال.',
                    $serviceType === 'otp' ? 'پنل OTP' : 'پنل تبلیغاتی',
                    number_format($balanceInfo['balance']),
                    number_format($requiredCost)
                ),
                $balanceInfo['balance'],
                $requiredCost,
                null,
                $pageCount !== null ? $this->maxAffordableRecipients($balanceInfo['balance'], $pageCount) : null
            );
        }

        return $balanceInfo;
    }

    /**
     * Bulk campaigns are promotional: an OTP template can never be used here, whether it comes
     * from the picker or a hand-crafted request.
     *
     * @throws DomainException
     */
    public function assertPromotionalTemplate(?int $templateId): void
    {
        if (!$templateId) {
            return;
        }

        $serviceType = SmsTemplate::whereKey($templateId)->value('service_type');
        if ($serviceType === 'otp') {
            throw new DomainException('قالب‌های کد تأیید (OTP) برای ارسال تبلیغاتی انبوه مجاز نیستند.');
        }
    }

    /**
     * Create and dispatch a bulk SMS campaign.
     *
     * @param array<string, mixed> $data
     * @param User $actor
     * @param GhorfeOnlineList|null $business
     * @return Groupsms
     * @throws InsufficientBalanceException|TierAccessDeniedException|DomainException
     */
    public function createCampaign(array $data, User $actor, ?GhorfeOnlineList $business = null): Groupsms
    {
        $business = $business ?? ghorfe();
        if (!$business) {
            throw new DomainException('کسب‌وکار معتبری برای ارسال پیامک یافت نشد.');
        }

        $recipientType = $data['recipient_type'] ?? 'normal';
        $filters = $data['filters'] ?? [];
        $type = $data['type'] ?? 'bulk';
        $serviceType = $this->resolveServiceType($data);

        if ($type === 'ai' && empty($data['human_confirmed'])) {
            throw new DomainException('ارسال کمپین هوش مصنوعی نیازمند تایید نهایی کاربر (Human Confirmation) می‌باشد.');
        }

        // 1. Enforce Tier Gate
        $this->accessTierService->assertCanQuery($actor, $filters, $recipientType, $business->id);
        $requiredTier = $this->accessTierService->getRequiredTier($filters, $recipientType, $business->id);

        // 2. Resolve sender number (promotional: the business' own lines; OTP: the system OTP line, Titanium only)
        $senderNumberId = !empty($data['sender_number_id']) ? (int) $data['sender_number_id'] : null;
        $senderNumber = $this->resolveSenderNumberFor($serviceType, $business, $senderNumberId, $actor);

        // OTP sends carry exactly one fixed system category and a classification; promotional ones neither.
        $otpCategory = $serviceType === 'otp' ? $this->singleSmsSendService->resolveOtpCategory($data) : null;

        // 3. Resolve template & message content.
        $templateId = !empty($data['sms_message_template_id']) ? (int) $data['sms_message_template_id'] : null;
        if ($serviceType !== 'otp') {
            $this->assertPromotionalTemplate($templateId);
        }
        $rawMessage = $data['message'] ?? '';
        if ($templateId && empty($rawMessage)) {
            $template = SmsTemplate::find($templateId);
            $rawMessage = $template?->content ?? '';
        }

        if (trim($rawMessage) === '') {
            throw new DomainException('متن پیامک نمی‌تواند خالی باشد.');
        }

        // 4. Resolve and de-duplicate recipients
        $filterResult = $this->recipientFilterService->filterRecipients($filters, $actor, $business, $recipientType);
        $validRecipients = $filterResult['valid_recipients'];
        $recipientCount = count($validRecipients);

        if ($recipientCount === 0) {
            throw new DomainException('هیچ مخاطب معتبری پس از اعمال فیلترها و اعتبارسنجی شماره‌ها یافت نشد.');
        }

        // 4b. Resolve placeholders
        $representativeUser = $this->resolveWorstCaseRecipient($validRecipients);
        $resolvedForEstimate = $this->singleSmsSendService->resolvePlaceholders($rawMessage, [], $representativeUser);
        $segmentation = $this->calculateMessageSegmentation($resolvedForEstimate, $serviceType);
        $pageCount = $segmentation['page_count'];

        // 4c. «کاهش تعداد مخاطبین»
        if (isset($data['max_recipients']) && (int) $data['max_recipients'] > 0) {
            $affordable = $this->maxAffordableRecipients($this->getBalance($business, $serviceType)['balance'], $pageCount);
            $cap = min((int) $data['max_recipients'], $affordable, $recipientCount);

            if ($cap <= 0) {
                $recipientCount = 0;
            } elseif ($cap < $recipientCount) {
                $validRecipients = array_slice($validRecipients, 0, $cap);
                $recipientCount = $cap;
            }

            if ($recipientCount === 0) {
                $this->verifyBalance($business, $this->calculateCampaignCost(1, $pageCount), $pageCount, $serviceType);
            }
        }

        // 5. Calculate total cost = recipients × pages × tariff
        $totalCost = $this->calculateCampaignCost($recipientCount, $pageCount);

        // 6. Hard block balance verification BEFORE any send or campaign queue
        $this->verifyBalance($business, $totalCost, $pageCount, $serviceType);

        // 7. Parse scheduled time if provided
        $scheduledAt = null;
        if (!empty($data['scheduled_at'])) {
            $scheduledAt = Carbon::parse($data['scheduled_at']);
        }

        $isScheduled = $scheduledAt !== null && $scheduledAt->isFuture();
        $status = $isScheduled ? 'scheduled' : 'sending';

        // 8. Atomic database transaction to persist campaign and recipient rows
        $campaign = DB::transaction(function () use (
            $business,
            $type,
            $templateId,
            $senderNumber,
            $filters,
            $requiredTier,
            $recipientCount,
            $status,
            $scheduledAt,
            $totalCost,
            $actor,
            $validRecipients,
            $rawMessage,
            $data,
            $serviceType,
            $otpCategory
        ) {
            // Subject folders and the classification level exist for OTP only.
            $groupId = $serviceType === 'otp' ? ($data['sms_message_group_id'] ?? $data['category_id'] ?? null) : null;
            $classification = $serviceType === 'otp' && ($data['classification'] ?? null) === 'classified' ? 'classified' : 'regular';

            $campaign = Groupsms::create([
                'title' => 'کمپین: ' . ($rawMessage ? Str::limit($rawMessage, 30) : 'پیامک انبوه'),
                'name' => 'کمپین: ' . ($rawMessage ? Str::limit($rawMessage, 30) : 'پیامک انبوه'),
                'ghorfe_online_list_id' => $business->id,
                'type' => $type,
                'ai_goal_prompt' => $data['ai_goal_prompt'] ?? null,
                'template_id' => $templateId,
                'group' => $groupId,
                'classification' => $classification,
                'sender_number_id' => $senderNumber->id,
                'sender_number' => $senderNumber->number,
                'sender_phone' => $senderNumber->number,
                'message' => $rawMessage,
                'content' => $rawMessage,
                'filters' => array_merge($filters, [
                    'message' => $rawMessage,
                    'message_preview' => Str::limit($rawMessage, 200),
                    'sms_message_group_id' => $groupId,
                    'classification' => $classification,
                ]),
                'required_tier' => $requiredTier,
                'recipient_count' => $recipientCount,
                'total_recipients' => $recipientCount,
                'sent_count' => 0,
                'success_count' => 0,
                'failed_count' => 0,
                'pending_count' => $recipientCount,
                'status' => $status,
                'scheduled_at' => $scheduledAt,
                'send_at' => $scheduledAt ? $scheduledAt->toDateTimeString() : now()->toDateTimeString(),
                'cost_estimate' => $totalCost,
                'created_by' => $actor->id,
                'is_bulk' => true,
                'direction' => 'outbound',
                'service_type' => $serviceType,
                'otp_category' => $otpCategory,
            ]);

            // Bulk insert recipients as child Groupsms records
            $now = now();
            $recipientChunks = array_chunk($validRecipients, 500);
            foreach ($recipientChunks as $chunk) {
                $rows = array_map(function ($r) use ($campaign, $now, $business, $senderNumber, $serviceType, $otpCategory) {
                    return [
                        // Raw insert bypasses the model, and the legacy table requires these three.
                        'title' => $campaign->title,
                        'message' => $campaign->message,
                        'flag' => 1,
                        'request_id' => $campaign->id,
                        'ghorfe_online_list_id' => $business->id,
                        'phones' => $r['phone'],
                        'recipient_phone' => $r['phone'],
                        'user_id' => $r['user_id'],
                        'callcenter_id' => $r['callcenter_id'] ?? null,
                        'status' => 'pending',
                        'delivery_status' => null,
                        'error_reason' => null,
                        'tracking_id' => null,
                        'is_bulk' => false,
                        'type' => 'bulk_recipient',
                        'direction' => 'outbound',
                        'service_type' => $serviceType,
                        'otp_category' => $otpCategory,
                        'sender_number_id' => $senderNumber->id,
                        'sender_number' => $senderNumber->number,
                        'sender_phone' => $senderNumber->number,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }, $chunk);

                Groupsms::insert($rows);
            }

            return $campaign;
        });

        // 9. Dispatch Queued Job
        if ($isScheduled) {
            SendBulkSmsCampaignJob::dispatch($campaign)->delay($scheduledAt);
        } else {
            SendBulkSmsCampaignJob::dispatch($campaign);
        }

        return $campaign;
    }

    /**
     * Dispatch single SMS to external gateway.
     */
    public function dispatchToGateway(string $smsPanel, string $phone, string $message): void
    {
        if ($this->gatewayDispatcher !== null) {
            ($this->gatewayDispatcher)($smsPanel, $phone, $message);
            return;
        }

        if (self::$globalGatewayDispatcher !== null) {
            (self::$globalGatewayDispatcher)($smsPanel, $phone, $message);
            return;
        }

        if (SmsGatewaySimulator::shouldSimulate($smsPanel)) {
            SmsGatewaySimulator::dispatch($smsPanel, $phone, $message);
            return;
        }

        if (strtolower($smsPanel) === 'neginsms') {
            NeginSms::sendSingleSms($message, $phone, 'single');
        } else {
            IranSms::sendSingleSms($phone, $message);
        }
    }

    /**
     * Execute the queued bulk campaign processing.
     * Called by SendBulkSmsCampaignJob.
     */
    public function executeCampaign(Groupsms $campaign): void
    {
        $campaign->refresh();

        // Only a live campaign can run: finished, failed or queue-only (pending_queue / draft)
        // campaigns are never sent from here.
        if (!in_array($campaign->status, ['scheduled', 'sending'], true)) {
            return;
        }

        // If scheduled and future, don't execute yet (under the sync queue the delayed job
        // runs immediately and lands here; sms:dispatch-due-campaigns picks it up later).
        if ($campaign->status === 'scheduled' && $campaign->scheduled_at && $campaign->scheduled_at->isFuture()) {
            return;
        }

        // One runner per campaign: the delayed job and the due-campaign command may race.
        $lock = Cache::lock('sms-campaign-execute:' . $campaign->id, 3600);
        if (!$lock->get()) {
            return;
        }

        try {
            $campaign->refresh();
            if (in_array($campaign->status, ['scheduled', 'sending'], true)) {
                $this->runCampaign($campaign);
            }
        } finally {
            $lock->release();
        }
    }

    /**
     * Find campaigns whose scheduled time has arrived and execute each exactly once.
     * Safe to call every minute (see sms:dispatch-due-campaigns): executeCampaign() holds a
     * per-campaign lock and a finished campaign leaves the "scheduled" status.
     *
     * @return int number of campaigns picked up
     */
    public function dispatchDueCampaigns(): int
    {
        $picked = 0;

        Groupsms::where('is_bulk', true)
            ->where('status', 'scheduled')
            ->where('scheduled_at', '<=', now())
            ->orderBy('scheduled_at')
            ->pluck('id')
            ->each(function ($id) use (&$picked) {
                $campaign = Groupsms::find($id);
                if ($campaign && $campaign->status === 'scheduled') {
                    $picked++;
                    $this->executeCampaign($campaign);
                }
            });

        return $picked;
    }

    /**
     * Park unsent compose-page content in the outgoing queue (status pending_queue):
     * no recipient rows, no balance debit, nothing sent. Tier-checked like a real send.
     * Re-saving the same content (or passing $existing) updates the row instead of duplicating it.
     *
     * @param array<string, mixed> $data same shape as createCampaign() (+ max_recipients ignored)
     * @throws TierAccessDeniedException|DomainException
     */
    public function saveToQueue(array $data, User $actor, GhorfeOnlineList $business, ?Groupsms $existing = null): Groupsms
    {
        $recipientType = $data['recipient_type'] ?? 'normal';
        $filters = (array) ($data['filters'] ?? []);

        $serviceType = $this->resolveServiceType($data);

        $this->accessTierService->assertCanQuery($actor, $filters, $recipientType, $business->id);
        $requiredTier = $this->accessTierService->getRequiredTier($filters, $recipientType, $business->id);

        if ($serviceType === 'otp' && !$this->accessTierService->canUseOtp($actor)) {
            throw new DomainException('دسترسی به سرویس پیامک OTP برای سطح دسترسی شما مجاز نیست.');
        }

        $templateId = !empty($data['sms_message_template_id']) ? (int) $data['sms_message_template_id'] : null;
        if ($serviceType !== 'otp') {
            $this->assertPromotionalTemplate($templateId);
        }

        $rawMessage = (string) ($data['message'] ?? '');
        if ($templateId && trim($rawMessage) === '') {
            $rawMessage = (string) SmsTemplate::whereKey($templateId)->value('content');
        }
        if (trim($rawMessage) === '') {
            throw new DomainException('متن پیامک نمی‌تواند خالی باشد.');
        }
        if (empty($filters)) {
            throw new DomainException('برای ذخیره در صف ارسال ابتدا مخاطبان را فیلتر کنید.');
        }

        $senderNumberId = $serviceType !== 'otp' && !empty($data['sender_number_id'])
            ? $this->resolveAdvertisingSenderNumber($business, (int) $data['sender_number_id'])->id
            : null;

        $groupId = $serviceType === 'otp' ? ($data['sms_message_group_id'] ?? null) : null;
        $filterResult = $this->recipientFilterService->filterRecipients($filters, $actor, $business, $recipientType);
        $recipientCount = (int) $filterResult['valid_count'];
        $representative = $this->resolveWorstCaseRecipient($filterResult['valid_recipients']);
        $pageCount = $this->calculateMessageSegmentation(
            $this->singleSmsSendService->resolvePlaceholders($rawMessage, [], $representative),
            $serviceType
        )['page_count'];

        $otpCategory = $serviceType === 'otp' && !empty($data['otp_category']) ? (string) $data['otp_category'] : null;
        $classification = $serviceType === 'otp' && ($data['classification'] ?? null) === 'classified' ? 'classified' : 'regular';

        $stored = array_merge($filters, [
            'message' => $rawMessage,
            'message_preview' => Str::limit($rawMessage, 200),
            'recipient_type' => $recipientType,
            'sms_message_group_id' => $groupId,
            'service_type' => $serviceType,
            'otp_category' => $otpCategory,
            'classification' => $classification,
        ]);

        // Same content saved again (beacon on every page leave) must not pile up duplicates.
        $fingerprint = sha1(json_encode([$rawMessage, $templateId, $senderNumberId, $filters, $recipientType, $serviceType, $otpCategory]));
        $existing ??= Groupsms::pendingQueue()
            ->where('ghorfe_online_list_id', $business->id)
            ->where('created_by', $actor->id)
            ->get()
            ->first(fn (Groupsms $c) => ($c->filters['_fingerprint'] ?? null) === $fingerprint);
        $stored['_fingerprint'] = $fingerprint;

        $attributes = [
            'type' => 'bulk',
            'template_id' => $templateId,
            'group' => $groupId,
            'sender_number_id' => $senderNumberId,
            'filters' => $stored,
            'required_tier' => $requiredTier,
            'recipient_count' => $recipientCount,
            'total_recipients' => $recipientCount,
            'pending_count' => $recipientCount,
            'status' => Groupsms::STATUS_PENDING_QUEUE,
            'scheduled_at' => !empty($data['scheduled_at']) ? Carbon::parse($data['scheduled_at']) : null,
            'cost_estimate' => $this->calculateCampaignCost($recipientCount, $pageCount),
            'is_bulk' => true,
            'service_type' => $serviceType,
            'otp_category' => $otpCategory,
        ];

        if ($existing && $existing->status === Groupsms::STATUS_PENDING_QUEUE
            && (int) $existing->ghorfe_online_list_id === (int) $business->id) {
            $existing->update($attributes);
            return $existing;
        }

        return Groupsms::create($attributes + [
            'ghorfe_online_list_id' => $business->id,
            'classification' => $classification,
            'sent_count' => 0,
            'success_count' => 0,
            'failed_count' => 0,
            'created_by' => $actor->id,
            'direction' => 'outbound',
        ]);
    }

    /**
     * Turn a queued entry back into compose-page input (message, template, sender, audience filter).
     *
     * @return array<string, mixed>
     */
    public function queuedCampaignToInput(Groupsms $queued): array
    {
        $stored = (array) $queued->filters;

        return [
            'recipient_type' => $stored['recipient_type'] ?? 'normal',
            'service_type' => $stored['service_type'] ?? ($queued->service_type ?: 'advertising'),
            'otp_category' => $stored['otp_category'] ?? $queued->otp_category,
            'classification' => $stored['classification'] ?? 'regular',
            'message' => $stored['message'] ?? ($stored['message_preview'] ?? ''),
            'sms_message_template_id' => $queued->template_id,
            'sms_message_group_id' => $queued->group,
            'sender_number_id' => $queued->sender_number_id,
            'scheduled_at' => $queued->scheduled_at,
            'filters' => Arr::except($stored, [
                'message', 'message_preview', 'recipient_type', 'sms_message_group_id',
                'classification', 'service_type', 'otp_category', '_fingerprint',
            ]),
        ];
    }

    /**
     * «ارسال اکنون» from the outgoing queue: runs the normal createCampaign() path (tier gate,
     * balance guard, template check) and removes the queue entry only once that succeeded.
     *
     * @throws InsufficientBalanceException|TierAccessDeniedException|DomainException
     */
    public function sendQueuedCampaign(Groupsms $queued, User $actor, GhorfeOnlineList $business, ?int $maxRecipients = null): Groupsms
    {
        if ((int) $queued->ghorfe_online_list_id !== (int) $business->id || $queued->status !== Groupsms::STATUS_PENDING_QUEUE) {
            throw new DomainException('این مورد در صف ارسال یافت نشد.');
        }

        // Claim the entry so a double click cannot send it twice; 'draft' = being processed.
        $claimed = Groupsms::whereKey($queued->id)
            ->where('status', Groupsms::STATUS_PENDING_QUEUE)
            ->update(['status' => 'draft']);
        if ($claimed !== 1) {
            throw new DomainException('این مورد در حال ارسال است یا قبلاً ارسال شده است.');
        }

        $input = $this->queuedCampaignToInput($queued);
        // A queued schedule that has already passed simply means "send now".
        if (!empty($input['scheduled_at']) && !Carbon::parse($input['scheduled_at'])->isFuture()) {
            $input['scheduled_at'] = null;
        }
        if ($maxRecipients) {
            $input['max_recipients'] = $maxRecipients;
        }

        try {
            $campaign = $this->createCampaign($input, $actor, $business);
        } catch (\Throwable $e) {
            Groupsms::whereKey($queued->id)->update(['status' => Groupsms::STATUS_PENDING_QUEUE]);
            throw $e;
        }

        $queued->delete();

        return $campaign;
    }

    /**
     * Execute a claimed campaign. Callers go through executeCampaign() for the guards + lock.
     */
    protected function runCampaign(Groupsms $campaign): void
    {
        $campaign->update(['status' => 'sending']);

        $serviceType = $campaign->service_type === 'otp' ? 'otp' : 'advertising';
        $business = $campaign->business;
        $senderNumber = $campaign->senderNumber;
        $creator = $campaign->creator;

        if (!$business || !$senderNumber) {
            $campaign->update(['status' => 'failed']);
            Log::error("Bulk Campaign #{$campaign->id} failed: Business or Sender Number missing.");
            return;
        }

        $template = $campaign->template;
        $rawMessage = $template?->content ?? ($campaign->filters['message'] ?? $campaign->filters['message_preview'] ?? $campaign->message ?? '');

        $pendingRecipients = $campaign->recipients()
            ->where('status', 'pending')
            ->with('user')
            ->get();
        $stoppedForBalance = false;

        foreach ($pendingRecipients as $recipientRecord) {
            $trackingId = 'BLK-' . strtoupper(Str::random(12));
            $recipientPhone = $recipientRecord->phone;
            $recipientUser = $recipientRecord->user;

            $resolvedContent = $this->singleSmsSendService->resolvePlaceholders($rawMessage, [], $recipientUser);
            $segmentation = $this->calculateMessageSegmentation($resolvedContent, $serviceType);
            $content = $segmentation['text'];
            $pageCount = $segmentation['page_count'];
            $characterCount = $segmentation['character_count'];
            $costPerMessage = $segmentation['cost_per_message'];

            try {
                DB::transaction(function () use (
                    $campaign,
                    $business,
                    $senderNumber,
                    $creator,
                    $recipientRecord,
                    $recipientPhone,
                    $recipientUser,
                    $content,
                    $characterCount,
                    $pageCount,
                    $costPerMessage,
                    $trackingId,
                    $serviceType
                ) {
                    // Lock the balance row, verify it covers this message, and debit atomically.
                    $balanceBefore = $business->lockAndDebit($costPerMessage, $serviceType);
                    $balanceAfter = $balanceBefore - $costPerMessage;

                    // Update child recipient record
                    $recipientRecord->update([
                        'content' => $content,
                        'message' => $content,
                        'character_count' => $characterCount,
                        'page_count' => $pageCount,
                        'status' => 'queued',
                        'cost' => $costPerMessage,
                        'tracking_id' => $trackingId,
                        'sender_role' => $this->audit->resolveSenderRole($creator),
                    ]);

                    // Create transaction record in sms_logs
                    SmsLog::create([
                        'ghorfe_online_list_id' => $business->id,
                        'credit_type' => $serviceType,
                        'sender_number_id' => $senderNumber->id,
                        'charge_amount' => 0,
                        'consume_amount' => $costPerMessage,
                        'balance_before' => $balanceBefore,
                        'balance_after' => $balanceAfter,
                        'sms_message_id' => $recipientRecord->id,
                        'sms_campaign_id' => $campaign->id,
                        'tracking_id' => $trackingId,
                        'created_by' => $creator?->id,
                        'message' => $content,
                        'sender_number' => $senderNumber->number,
                        'phone' => $recipientPhone,
                        'type' => $serviceType,
                        'status' => 'queued',
                        'response' => json_encode([
                            'sender_role' => $this->audit->resolveSenderRole($creator),
                            'channel' => SmsAuditService::CHANNEL_BULK,
                        ]),
                    ]);

                    // Dispatch to gateway
                    try {
                        $this->dispatchToGateway(
                            $senderNumber->sms_panel ?? 'IranSms',
                            $recipientPhone,
                            $content
                        );

                        $recipientRecord->update([
                            'status' => 'sent',
                            'delivery_status' => 'delivered',
                            'tracking_id' => $trackingId,
                        ]);

                        $campaign->increment('sent_count');
                        $campaign->increment('success_count');
                        $campaign->decrement('pending_count');
                    } catch (\Throwable $e) {
                        Log::error("Bulk SMS Gateway Error for Campaign #{$campaign->id}: " . $e->getMessage(), [
                            'phone' => $recipientPhone,
                            'tracking_id' => $trackingId,
                        ]);

                        $recipientRecord->update([
                            'status' => 'failed',
                            'error_reason' => $e->getMessage(),
                            'tracking_id' => $trackingId,
                        ]);

                        $campaign->increment('sent_count');
                        $campaign->increment('failed_count');
                        $campaign->decrement('pending_count');
                    }
                });
            } catch (InsufficientBalanceException $e) {
                $stoppedForBalance = true;

                Log::warning("Bulk Campaign #{$campaign->id} stopped: insufficient balance.", [
                    'sender_number_id' => $senderNumber->id,
                    'message' => $e->getMessage(),
                ]);

                $stopped = $campaign->recipients()
                    ->where('status', 'pending')
                    ->update([
                        'status' => 'failed',
                        'error_reason' => $e->getMessage(),
                    ]);

                $campaign->increment('failed_count', $stopped);
                $campaign->decrement('pending_count', $stopped);

                break;
            }
        }

        $campaign->refresh();
        if ($campaign->failed_count === $campaign->total_recipients && $campaign->total_recipients > 0) {
            $campaign->update(['status' => 'failed']);
        } elseif ($stoppedForBalance && $campaign->failed_count > 0) {
            $campaign->update(['status' => 'completed_with_errors']);
        } else {
            $campaign->update(['status' => 'completed']);
        }
    }
}
