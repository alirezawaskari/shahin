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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
        protected SmsAccessTierService $accessTierService
    ) {
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
     * Get financial balance for business and advertising sender number.
     *
     * @return array{balance: float, model: AdvertisingBalance, credit_type: string}
     */
    public function getBalance(GhorfeOnlineList $business, SenderNumber $senderNumber): array
    {
        $model = AdvertisingBalance::firstOrCreate(
            [
                'ghorfe_online_list_id' => $business->id,
                'sender_number_id' => $senderNumber->id,
            ],
            ['balance' => 0]
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
    public function calculateMessageSegmentation(string $content, bool $autoAppendCancel = true): array
    {
        $segmentation = $this->singleSmsSendService->calculateSegmentation($content, $autoAppendCancel, 'advertising');
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
     * Check balance sufficiency and throw InsufficientBalanceException if insufficient.
     *
     * @throws InsufficientBalanceException
     */
    public function verifyBalance(GhorfeOnlineList $business, SenderNumber $senderNumber, float $requiredCost): array
    {
        $balanceInfo = $this->getBalance($business, $senderNumber);

        if ($balanceInfo['balance'] < $requiredCost) {
            throw new InsufficientBalanceException(
                sprintf(
                    'موجودی شماره فرستنده برای این کمپین کافی نیست. موجودی فعلی: %s ریال، هزینه مورد نیاز: %s ریال.',
                    number_format($balanceInfo['balance']),
                    number_format($requiredCost)
                ),
                $balanceInfo['balance'],
                $requiredCost
            );
        }

        return $balanceInfo;
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
        $this->accessTierService->assertCanQuery($actor, $filters, $recipientType);
        $requiredTier = $this->accessTierService->getRequiredTier($filters, $recipientType);

        // 2. Resolve sender number (strictly restricted to business's assigned advertising numbers)
        $senderNumberId = !empty($data['sender_number_id']) ? (int) $data['sender_number_id'] : null;
        $senderNumber = $this->resolveAdvertisingSenderNumber($business, $senderNumberId);

        // 3. Resolve template & message content
        $templateId = !empty($data['sms_message_template_id']) ? (int) $data['sms_message_template_id'] : null;
        $rawMessage = $data['message'] ?? '';
        if ($templateId && empty($rawMessage)) {
            $template = SmsMessageTemplate::find($templateId);
            $rawMessage = $template?->content ?? '';
        }

        if (trim($rawMessage) === '') {
            throw new DomainException('متن پیامک نمی‌تواند خالی باشد.');
        }

        $autoAppendCancel = (bool) ($data['auto_append_cancel'] ?? true);
        $segmentation = $this->calculateMessageSegmentation($rawMessage, $autoAppendCancel);
        $pageCount = $segmentation['page_count'];

        // 4. Resolve and de-duplicate recipients
        $filterResult = $this->recipientFilterService->filterRecipients($filters, $actor, $business, $recipientType);
        $validRecipients = $filterResult['valid_recipients'];
        $recipientCount = count($validRecipients);

        if ($recipientCount === 0) {
            throw new DomainException('هیچ مخاطب معتبری پس از اعمال فیلترها و اعتبارسنجی شماره‌ها یافت نشد.');
        }

        // 5. Calculate total cost = recipients × pages × tariff
        $totalCost = $this->calculateCampaignCost($recipientCount, $pageCount);

        // 6. Hard block balance verification BEFORE any send or campaign queue
        $this->verifyBalance($business, $senderNumber, $totalCost);

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
            $campaign = SmsCampaign::create([
                'ghorfe_online_list_id' => $business->id,
                'type' => $type,
                'ai_goal_prompt' => $data['ai_goal_prompt'] ?? null,
                'sms_message_template_id' => $templateId,
                'sender_number_id' => $senderNumber->id,
                'filters' => array_merge($filters, ['message_preview' => Str::limit($rawMessage, 200)]),
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

        // If scheduled and future, don't execute yet
        if ($campaign->status === 'scheduled' && $campaign->scheduled_at && $campaign->scheduled_at->isFuture()) {
            return;
        }

        $campaign->update(['status' => 'sending']);

        $business = $campaign->business;
        $senderNumber = $campaign->senderNumber;
        $creator = $campaign->creator;

        if (!$business || !$senderNumber) {
            $campaign->update(['status' => 'failed']);
            Log::error("Bulk Campaign #{$campaign->id} failed: Business or Sender Number missing.");
            return;
        }

        // Template or raw message
        $template = $campaign->template;
        $rawMessage = $template?->content ?? ($campaign->filters['message'] ?? $campaign->filters['message_preview'] ?? '');

        $autoAppendCancel = true;
        $segmentation = $this->calculateMessageSegmentation($rawMessage, $autoAppendCancel);
        $pageCount = $segmentation['page_count'];
        $costPerMessage = $segmentation['cost_per_message'];
        $baseContent = $segmentation['text'];
        $tariff = $segmentation['tariff'];

        $pendingRecipients = $campaign->recipients()->where('status', 'pending')->with('user')->get();

        foreach ($pendingRecipients as $recipientRecord) {
            $trackingId = 'BLK-' . strtoupper(Str::random(12));
            $recipientPhone = $recipientRecord->phone;
            $recipientUser = $recipientRecord->user;

            // Resolve dynamic placeholders
            $content = $this->singleSmsSendService->resolvePlaceholders($baseContent, [], $recipientUser);

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
                    $pageCount,
                    $costPerMessage,
                    $trackingId
                ) {
                    // Lock the balance row, verify it covers this message, and debit atomically.
                    // Throws InsufficientBalanceException, which rolls back this recipient's transaction.
                    $balanceModel = $this->getBalance($business, $senderNumber)['model'];
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
                        'content' => $content,
                        'character_count' => mb_strlen($content, 'UTF-8'),
                        'page_count' => $pageCount,
                        'status' => 'queued',
                        'cost' => $costPerMessage,
                        'tracking_id' => $trackingId,
                        'user_id' => $recipientUser?->id,
                        'created_by' => $creator?->id,
                        'is_bulk' => true,
                        'sms_campaign_id' => $campaign->id,
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
        } else {
            $campaign->update(['status' => 'completed']);
        }
    }
}
