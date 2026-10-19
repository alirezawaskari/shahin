<?php

namespace App\Services\Sms;

use App\Classes\External\IranSms;
use App\Classes\External\NeginSms;
use App\Exceptions\Sms\InsufficientBalanceException;
use App\Jobs\SendBulkSmsCampaignJob;
use App\Models\AdvertisingBalance;
use App\Models\GhorfeOnlineList;
use App\Models\SenderNumber;
use App\Models\SmsCampaign;
use App\Models\SmsCampaignRecipient;
use App\Models\SmsMessage;
use App\Models\SmsMessageTemplate;
use App\Models\SmsTransaction;
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
     * Resolve and validate advertising sender number for a given business.
     */
    public function resolveAdvertisingSenderNumber(GhorfeOnlineList $business, ?int $requestedSenderNumberId = null): SenderNumber
    {
        $query = $business->senderNumbers()
            ->where('sender_numbers.type', 'advertising')
            ->where('sender_numbers.status', 'active');

        if ($requestedSenderNumberId) {
            $sender = (clone $query)->where('sender_numbers.id', $requestedSenderNumberId)->first();
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
     * @return array{balance: float, model: AdvertisingBalance, credit_type: string}
     */
    public function getBalance(GhorfeOnlineList $business): array
    {
        $model = AdvertisingBalance::firstOrCreate(
            ['ghorfe_online_list_id' => $business->id],
            []
        );

        return [
            'balance' => (float) $model->balance,
            'model' => $model,
            'credit_type' => 'advertising',
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
    public function calculateMessageSegmentation(string $content): array
    {
        $segmentation = $this->singleSmsSendService->calculateSegmentation($content, 'advertising');
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
    public function verifyBalance(GhorfeOnlineList $business, float $requiredCost, ?int $pageCount = null): array
    {
        $balanceInfo = $this->getBalance($business);

        if ($balanceInfo['balance'] < $requiredCost) {
            throw new InsufficientBalanceException(
                sprintf(
                    'موجودی پنل تبلیغاتی برای این کمپین کافی نیست. موجودی فعلی: %s ریال، هزینه مورد نیاز: %s ریال.',
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

        $serviceType = SmsMessageTemplate::whereKey($templateId)->value('service_type');
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
     * @return SmsCampaign
     * @throws InsufficientBalanceException|TierAccessDeniedException|DomainException
     */
    public function createCampaign(array $data, User $actor, ?GhorfeOnlineList $business = null): SmsCampaign
    {
        $business = $business ?? ghorfe();
        if (!$business) {
            throw new DomainException('کسب‌وکار معتبری برای ارسال پیامک یافت نشد.');
        }

        $recipientType = $data['recipient_type'] ?? 'normal';
        $filters = $data['filters'] ?? [];
        $type = $data['type'] ?? 'bulk';

        if ($type === 'ai' && empty($data['human_confirmed'])) {
            throw new DomainException('ارسال کمپین هوش مصنوعی نیازمند تایید نهایی کاربر (Human Confirmation) می‌باشد.');
        }

        // 1. Enforce Tier Gate
        $this->accessTierService->assertCanQuery($actor, $filters, $recipientType, $business->id);
        $requiredTier = $this->accessTierService->getRequiredTier($filters, $recipientType, $business->id);

        // 2. Resolve sender number (strictly restricted to business's assigned advertising numbers)
        $senderNumberId = !empty($data['sender_number_id']) ? (int) $data['sender_number_id'] : null;
        $senderNumber = $this->resolveAdvertisingSenderNumber($business, $senderNumberId);

        // 3. Resolve template & message content. $rawMessage is still
        // unresolved here — it may contain literal placeholder tokens such
        // as "{نام کاربر}" — and must NOT be segmented/counted directly (see
        // below, after recipients are known).
        $templateId = !empty($data['sms_message_template_id']) ? (int) $data['sms_message_template_id'] : null;
        $this->assertPromotionalTemplate($templateId);
        $rawMessage = $data['message'] ?? '';
        if ($templateId && empty($rawMessage)) {
            $template = SmsMessageTemplate::find($templateId);
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

        // 4b. Resolve placeholders (against a worst-case stand-in recipient —
        // see resolveWorstCaseRecipient()), THEN append the mandatory لغو 11
        // opt-out and segment, matching SingleSmsSendService::send()'s order:
        // resolve → append لغو 11 → segment/count. Doing this on the raw,
        // unresolved template (the previous behavior) under-counts whenever
        // placeholders expand into text, which under-estimates both the
        // page_count used for the balance check below and the per-recipient
        // cost that executeCampaign() will actually charge.
        $representativeUser = $this->resolveWorstCaseRecipient($validRecipients);
        $resolvedForEstimate = $this->singleSmsSendService->resolvePlaceholders($rawMessage, [], $representativeUser);
        $segmentation = $this->calculateMessageSegmentation($resolvedForEstimate);
        $pageCount = $segmentation['page_count'];

        // 4c. «کاهش تعداد مخاطبین»: the client may ask to cap the audience, but the cap is never
        // trusted beyond what the balance can actually pay for (computed here, server-side).
        if (isset($data['max_recipients']) && (int) $data['max_recipients'] > 0) {
            $affordable = $this->maxAffordableRecipients($this->getBalance($business)['balance'], $pageCount);
            $cap = min((int) $data['max_recipients'], $affordable, $recipientCount);

            if ($cap <= 0) {
                $recipientCount = 0;
            } elseif ($cap < $recipientCount) {
                $validRecipients = array_slice($validRecipients, 0, $cap);
                $recipientCount = $cap;
            }

            if ($recipientCount === 0) {
                // Nothing affordable: report it as the usual insufficient-balance failure.
                $this->verifyBalance($business, $this->calculateCampaignCost(1, $pageCount), $pageCount);
            }
        }

        // 5. Calculate total cost = recipients × pages × tariff
        $totalCost = $this->calculateCampaignCost($recipientCount, $pageCount);

        // 6. Hard block balance verification BEFORE any send or campaign queue
        $this->verifyBalance($business, $totalCost, $pageCount);

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
            $data
        ) {
            $groupId = $data['sms_message_group_id'] ?? $data['category_id'] ?? null;
            // Advertising/bulk messages must never be classified; only OTP messages can be confidential (محرمانه).
            $classification = 'regular';

            $campaign = SmsCampaign::create([
                'ghorfe_online_list_id' => $business->id,
                'type' => $type,
                'ai_goal_prompt' => $data['ai_goal_prompt'] ?? null,
                'sms_message_template_id' => $templateId,
                'sms_message_group_id' => $groupId,
                'classification' => $classification,
                'sender_number_id' => $senderNumber->id,
                'filters' => array_merge($filters, [
                    'message_preview' => Str::limit($rawMessage, 200),
                    'sms_message_group_id' => $groupId,
                    'classification' => $classification,
                ]),
                'access_tier_required' => $requiredTier,
                'total_recipients' => $recipientCount,
                'sent_count' => 0,
                'success_count' => 0,
                'failed_count' => 0,
                'pending_count' => $recipientCount,
                'status' => $status,
                'scheduled_at' => $scheduledAt,
                'cost_estimate' => $totalCost,
                'created_by' => $actor->id,
            ]);

            // Bulk insert recipients
            $now = now();
            $recipientChunks = array_chunk($validRecipients, 500);
            foreach ($recipientChunks as $chunk) {
                $rows = array_map(function ($r) use ($campaign, $now) {
                    return [
                        'sms_campaign_id' => $campaign->id,
                        'phone' => $r['phone'],
                        'user_id' => $r['user_id'],
                        'callcenter_id' => $r['callcenter_id'] ?? null,
                        'status' => 'pending',
                        'delivery_status' => null,
                        'error_reason' => null,
                        'tracking_id' => null,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }, $chunk);

                SmsCampaignRecipient::insert($rows);
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
    public function executeCampaign(SmsCampaign $campaign): void
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

        SmsCampaign::where('status', 'scheduled')
            ->where('scheduled_at', '<=', now())
            ->orderBy('scheduled_at')
            ->pluck('id')
            ->each(function ($id) use (&$picked) {
                $campaign = SmsCampaign::find($id);
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
    public function saveToQueue(array $data, User $actor, GhorfeOnlineList $business, ?SmsCampaign $existing = null): SmsCampaign
    {
        $recipientType = $data['recipient_type'] ?? 'normal';
        $filters = (array) ($data['filters'] ?? []);

        $this->accessTierService->assertCanQuery($actor, $filters, $recipientType, $business->id);
        $requiredTier = $this->accessTierService->getRequiredTier($filters, $recipientType, $business->id);

        $templateId = !empty($data['sms_message_template_id']) ? (int) $data['sms_message_template_id'] : null;
        $this->assertPromotionalTemplate($templateId);

        $rawMessage = (string) ($data['message'] ?? '');
        if ($templateId && trim($rawMessage) === '') {
            $rawMessage = (string) SmsMessageTemplate::whereKey($templateId)->value('content');
        }
        if (trim($rawMessage) === '') {
            throw new DomainException('متن پیامک نمی‌تواند خالی باشد.');
        }
        if (empty($filters)) {
            throw new DomainException('برای ذخیره در صف ارسال ابتدا مخاطبان را فیلتر کنید.');
        }

        $senderNumberId = !empty($data['sender_number_id'])
            ? $this->resolveAdvertisingSenderNumber($business, (int) $data['sender_number_id'])->id
            : null;

        $groupId = $data['sms_message_group_id'] ?? null;
        $filterResult = $this->recipientFilterService->filterRecipients($filters, $actor, $business, $recipientType);
        $recipientCount = (int) $filterResult['valid_count'];
        $representative = $this->resolveWorstCaseRecipient($filterResult['valid_recipients']);
        $pageCount = $this->calculateMessageSegmentation(
            $this->singleSmsSendService->resolvePlaceholders($rawMessage, [], $representative)
        )['page_count'];

        $stored = array_merge($filters, [
            'message' => $rawMessage,
            'message_preview' => Str::limit($rawMessage, 200),
            'recipient_type' => $recipientType,
            'sms_message_group_id' => $groupId,
        ]);

        // Same content saved again (beacon on every page leave) must not pile up duplicates.
        $fingerprint = sha1(json_encode([$rawMessage, $templateId, $senderNumberId, $filters, $recipientType]));
        $existing ??= SmsCampaign::pendingQueue()
            ->where('ghorfe_online_list_id', $business->id)
            ->where('created_by', $actor->id)
            ->get()
            ->first(fn (SmsCampaign $c) => ($c->filters['_fingerprint'] ?? null) === $fingerprint);
        $stored['_fingerprint'] = $fingerprint;

        $attributes = [
            'type' => 'bulk',
            'sms_message_template_id' => $templateId,
            'sms_message_group_id' => $groupId,
            'sender_number_id' => $senderNumberId,
            'filters' => $stored,
            'access_tier_required' => $requiredTier,
            'total_recipients' => $recipientCount,
            'pending_count' => $recipientCount,
            'status' => SmsCampaign::STATUS_PENDING_QUEUE,
            'scheduled_at' => !empty($data['scheduled_at']) ? Carbon::parse($data['scheduled_at']) : null,
            'cost_estimate' => $this->calculateCampaignCost($recipientCount, $pageCount),
        ];

        if ($existing && $existing->status === SmsCampaign::STATUS_PENDING_QUEUE
            && (int) $existing->ghorfe_online_list_id === (int) $business->id) {
            $existing->update($attributes);
            return $existing;
        }

        return SmsCampaign::create($attributes + [
            'ghorfe_online_list_id' => $business->id,
            'classification' => 'regular',
            'sent_count' => 0,
            'success_count' => 0,
            'failed_count' => 0,
            'created_by' => $actor->id,
        ]);
    }

    /**
     * Turn a queued entry back into compose-page input (message, template, sender, audience filter).
     *
     * @return array<string, mixed>
     */
    public function queuedCampaignToInput(SmsCampaign $queued): array
    {
        $stored = (array) $queued->filters;

        return [
            'recipient_type' => $stored['recipient_type'] ?? 'normal',
            'message' => $stored['message'] ?? ($stored['message_preview'] ?? ''),
            'sms_message_template_id' => $queued->sms_message_template_id,
            'sms_message_group_id' => $queued->sms_message_group_id,
            'sender_number_id' => $queued->sender_number_id,
            'scheduled_at' => $queued->scheduled_at,
            'filters' => Arr::except($stored, [
                'message', 'message_preview', 'recipient_type', 'sms_message_group_id',
                'classification', '_fingerprint',
            ]),
        ];
    }

    /**
     * «ارسال اکنون» from the outgoing queue: runs the normal createCampaign() path (tier gate,
     * balance guard, template check) and removes the queue entry only once that succeeded.
     *
     * @throws InsufficientBalanceException|TierAccessDeniedException|DomainException
     */
    public function sendQueuedCampaign(SmsCampaign $queued, User $actor, GhorfeOnlineList $business, ?int $maxRecipients = null): SmsCampaign
    {
        if ((int) $queued->ghorfe_online_list_id !== (int) $business->id || $queued->status !== SmsCampaign::STATUS_PENDING_QUEUE) {
            throw new DomainException('این مورد در صف ارسال یافت نشد.');
        }

        // Claim the entry so a double click cannot send it twice; 'draft' = being processed.
        $claimed = SmsCampaign::whereKey($queued->id)
            ->where('status', SmsCampaign::STATUS_PENDING_QUEUE)
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
            SmsCampaign::whereKey($queued->id)->update(['status' => SmsCampaign::STATUS_PENDING_QUEUE]);
            throw $e;
        }

        $queued->delete();

        return $campaign;
    }

    /**
     * Execute a claimed campaign. Callers go through executeCampaign() for the guards + lock.
     */
    protected function runCampaign(SmsCampaign $campaign): void
    {
        $campaign->update(['status' => 'sending']);

        $business = $campaign->business;
        $senderNumber = $campaign->senderNumber;
        $creator = $campaign->creator;

        if (!$business || !$senderNumber) {
            $campaign->update(['status' => 'failed']);
            Log::error("Bulk Campaign #{$campaign->id} failed: Business or Sender Number missing.");
            return;
        }

        // Template or raw message. $rawMessage is unresolved — it may still
        // contain literal placeholder tokens such as "{نام کاربر}" — and is
        // deliberately NOT segmented/counted here: that must happen per
        // recipient, after placeholder resolution (inside the loop below),
        // otherwise every recipient's page_count/cost would be computed from
        // the same pre-resolution text regardless of their actual resolved
        // message length.
        $template = $campaign->template;
        $rawMessage = $template?->content ?? ($campaign->filters['message'] ?? $campaign->filters['message_preview'] ?? '');

        $pendingRecipients = $campaign->recipients()->where('status', 'pending')->with('user')->get();
        $stoppedForBalance = false;

        foreach ($pendingRecipients as $recipientRecord) {
            $trackingId = 'BLK-' . strtoupper(Str::random(12));
            $recipientPhone = $recipientRecord->phone;
            $recipientUser = $recipientRecord->user;

            // Resolve dynamic placeholders first, THEN append the mandatory
            // لغو 11 opt-out and segment on the final per-recipient text —
            // matches SingleSmsSendService::send()'s order (resolve → append
            // لغو 11 → segment/count), so page_count/character_count/cost
            // reflect what this specific recipient actually receives.
            $resolvedContent = $this->singleSmsSendService->resolvePlaceholders($rawMessage, [], $recipientUser);
            $segmentation = $this->calculateMessageSegmentation($resolvedContent);
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
                    $trackingId
                ) {
                    // Lock the balance row, verify it covers this message, and debit atomically.
                    // Throws InsufficientBalanceException, which rolls back this recipient's transaction.
                    $balanceModel = $this->getBalance($business)['model'];
                    $balanceBefore = $balanceModel->lockAndDebit($costPerMessage);
                    $balanceAfter = $balanceBefore - $costPerMessage;

                    // Create sms_message record
                    $smsMessage = SmsMessage::create([
                        'ghorfe_online_list_id' => $business->id,
                        'direction' => 'outbound',
                        'service_type' => 'advertising',
                        'sender_number_id' => $senderNumber->id,
                        'recipient_phone' => $recipientPhone,
                        'sender_phone' => $senderNumber->number,
                        'sms_message_template_id' => $campaign->sms_message_template_id,
                        'sms_message_group_id' => $campaign->sms_message_group_id ?? ($campaign->filters['sms_message_group_id'] ?? null),
                        'classification' => $campaign->classification ?? ($campaign->filters['classification'] ?? 'regular'),
                        'content' => $content,
                        'character_count' => $characterCount,
                        'page_count' => $pageCount,
                        'status' => 'queued',
                        'cost' => $costPerMessage,
                        'tracking_id' => $trackingId,
                        'user_id' => $recipientUser?->id,
                        'callcenter_id' => $recipientRecord->callcenter_id,
                        'created_by' => $creator?->id,
                        'sender_role' => $this->audit->resolveSenderRole($creator),
                        'is_bulk' => true,
                        'sms_campaign_id' => $campaign->id,
                    ]);

                    $this->audit->record($creator, SmsAuditService::CHANNEL_BULK, [
                        'sms_message' => $smsMessage,
                    ]);

                    // Create sms_transaction record
                    SmsTransaction::create([
                        'ghorfe_online_list_id' => $business->id,
                        'credit_type' => 'advertising',
                        'sender_number_id' => $senderNumber->id,
                        'charge_amount' => 0,
                        'consume_amount' => $costPerMessage,
                        'balance_before' => $balanceBefore,
                        'balance_after' => $balanceAfter,
                        'sms_message_id' => $smsMessage->id,
                        'sms_campaign_id' => $campaign->id,
                        'tracking_id' => $trackingId,
                        'created_by' => $creator?->id,
                    ]);

                    // Legacy backward-compatibility record
                    try {
                        \App\Models\Groupsms::create([
                            'title' => 'پیامک گروهی: ' . ($campaign->name ?? 'کمپین انبوه'),
                            'message' => $content,
                            'phones' => $recipientPhone,
                            'type' => 'groupsService',
                            'status' => 2,
                            'flag' => true,
                            'send_at' => now(),
                            'ghorfe_online_list_id' => $business?->id,
                            'sms_message_group_id' => $campaign->sms_message_group_id,
                            'classification' => $campaign->classification ?? 'regular',
                            'sms_message_id' => $smsMessage->id,
                        ]);
                    } catch (\Throwable) {}

                    // Dispatch to gateway
                    try {
                        $this->dispatchToGateway(
                            $senderNumber->sms_panel ?? 'IranSms',
                            $recipientPhone,
                            $content
                        );

                        $smsMessage->update(['status' => 'sent']);
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

                        $smsMessage->update([
                            'status' => 'failed',
                            'error_reason' => $e->getMessage(),
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
                // The sender balance can no longer cover a message: fail this recipient and
                // every remaining pending one (the rolled-back row is still pending), then stop.
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
            // Stopped by balance exhaustion with some recipients unsent: not a clean completion.
            $campaign->update(['status' => 'completed_with_errors']);
        } else {
            $campaign->update(['status' => 'completed']);
        }
    }
}
