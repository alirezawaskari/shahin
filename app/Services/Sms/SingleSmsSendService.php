<?php

namespace App\Services\Sms;

use App\Classes\External\IranSms;
use App\Enums\SmsOtpCategory;
use App\Classes\External\NeginSms;
use App\Exceptions\Sms\InsufficientBalanceException;
use App\Models\AdvertisingBalance;
use App\Models\GhorfeOnlineList;
use App\Models\History;
use App\Models\OtpBalance;
use App\Models\SenderNumber;
use App\Models\SmsMessage;
use App\Models\SmsMessageTemplate;
use App\Models\SmsTransaction;
use App\Models\User;
use App\Services\Settings\SettingsService;
use Carbon\Carbon;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SingleSmsSendService
{
    /**
     * Appended text for mandatory advertising opt-out in Iran telecom.
     */
    public const CANCEL_PHRASE = 'لغو 11';

    /**
     * Optional custom gateway dispatcher for testing or dependency override.
     *
     * @var callable|null
     */
    protected $gatewayDispatcher = null;

    public function __construct(
        protected DynamicParameterResolver $dynamicParameterResolver,
        protected ?SettingsService $settingsService = null,
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
     * Detect whether a string contains Unicode characters (e.g. Persian/Arabic/emojis)
     * or adheres strictly to the GSM-7 ASCII printable set.
     */
    public function isUnicode(string $text): bool
    {
        if ($text === '') {
            return false;
        }

        // Any character outside ASCII 0x20-0x7E, newline, carriage return, and tab is treated as Unicode
        return (bool) preg_match('/[^\x20-\x7E\r\n\t]/u', $text);
    }

    /**
     * Calculate SMS segmentation parameters (character count, remaining characters, page count).
     *
     * Iranian telecom provider rules (ParsaSms / IranSms / NeginSms / MCI / Irancell):
     * - Unicode (Persian):
     *   Page 1: 1 - 70 chars (max 70)
     *   Page 2: 71 - 134 chars (max 134, i.e. 70 + 64)
     *   Page 3+: 135 - 201 chars, 202 - 268 chars (67 chars per additional segment)
     *
     * - GSM-7 (English / ASCII):
     *   Page 1: 1 - 160 chars (max 160)
     *   Page 2: 161 - 306 chars (max 306, i.e. 153 * 2)
     *   Page 3+: 307 - 459 chars, 460 - 612 chars (153 chars per segment)
     *
     * @param string $text
     * @param string $serviceType
     * @return array<string, mixed>
     */
    public function calculateSegmentation(string $text, string $serviceType = 'normal'): array
    {
        $appendedCancel = false;

        // "لغو 11" is a mandatory telecom-regulatory requirement for advertising messages.
        // It is ALWAYS appended, unconditionally — there is no setting or toggle to disable it.
        if ($serviceType === 'advertising') {
            if (!str_contains($text, self::CANCEL_PHRASE)) {
                $text = rtrim($text) . "\n" . self::CANCEL_PHRASE;
                $appendedCancel = true;
            }
        }

        $length = mb_strlen($text, 'UTF-8');
        $isUnicode = $this->isUnicode($text);

        if ($length === 0) {
            return [
                'text' => $text,
                'character_count' => 0,
                'page_count' => 1,
                'remaining_characters' => $isUnicode ? 70 : 160,
                'is_unicode' => $isUnicode,
                'encoding' => $isUnicode ? 'Unicode' : 'GSM-7',
                'appended_cancel' => $appendedCancel,
            ];
        }

        if ($isUnicode) {
            // Persian / Unicode rules
            if ($length <= 70) {
                $pageCount = 1;
                $remaining = 70 - $length;
            } elseif ($length <= 134) {
                $pageCount = 2;
                $remaining = 134 - $length;
            } else {
                $pageCount = 2 + (int) ceil(($length - 134) / 67);
                $maxForPages = 134 + ($pageCount - 2) * 67;
                $remaining = $maxForPages - $length;
            }
        } else {
            // GSM-7 rules
            if ($length <= 160) {
                $pageCount = 1;
                $remaining = 160 - $length;
            } elseif ($length <= 306) {
                $pageCount = 2;
                $remaining = 306 - $length;
            } else {
                $pageCount = (int) ceil($length / 153);
                $maxForPages = $pageCount * 153;
                $remaining = $maxForPages - $length;
            }
        }

        return [
            'text' => $text,
            'character_count' => $length,
            'page_count' => $pageCount,
            'remaining_characters' => $remaining,
            'is_unicode' => $isUnicode,
            'encoding' => $isUnicode ? 'Unicode' : 'GSM-7',
            'appended_cancel' => $appendedCancel,
        ];
    }

    /**
     * Calculate cost based on page count.
     */
    public function calculateCost(int $pageCount, bool $isUnicode = true): float
    {
        $costPerPage = (float) config('services.sms.cost_per_page', 1200);

        return (float) ($pageCount * $costPerPage);
    }

    /**
     * Resolve the sender number adhering to strict business rules:
     * - OTP: auto-selected singleton system number, user override is strictly forbidden.
     * - Advertising: must be assigned to the current business via business_sender_number.
     */
    public function resolveSenderNumber(
        string $serviceType,
        GhorfeOnlineList $business,
        ?int $requestedSenderNumberId = null,
        ?User $actor = null
    ): SenderNumber {
        if ($serviceType === 'otp') {
            // OTP is Titanium-only. A system caller (no signed-in user, no actor) is not an end user and passes.
            $actor = $actor ?? auth()->user();
            if ($actor && !app(SmsAccessTierService::class)->canUseOtp($actor)) {
                throw new DomainException('دسترسی به سرویس پیامک اعتبارسنجی (OTP) برای سطح دسترسی شما مجاز نیست.');
            }

            // OTP sends NEVER allow a sender-number override.
            // Always resolve the active OTP system sender number.
            $otpSender = SenderNumber::query()
                ->where('type', 'otp')
                ->where('status', 'active')
                ->first();

            if (!$otpSender) {
                throw new DomainException('هیچ شماره فعال ارسال OTP در سیستم ثبت نشده است.');
            }

            return $otpSender;
        }

        // Advertising / normal: Must be assigned to the business through business_sender_number
        $businessNumbersQuery = $business->senderNumbers()
            ->where('sender_numbers.type', 'advertising')
            ->where('sender_numbers.status', 'active');

        if ($requestedSenderNumberId) {
            $sender = (clone $businessNumbersQuery)
                ->where('sender_numbers.id', $requestedSenderNumberId)
                ->first();

            if (!$sender) {
                throw new DomainException('شماره فرستنده انتخابی به این کسب‌وکار تخصیص نیافته است یا فعال نمی‌باشد.');
            }

            return $sender;
        }

        $sender = $businessNumbersQuery->first();

        if (!$sender) {
            throw new DomainException('هیچ شماره ارسال پیامک تبلیغاتی به این کسب‌وکار تخصیص نیافته است.');
        }

        return $sender;
    }

    /**
     * Retrieve the financial balance for the given service type. Advertising credit is shared by all of the business' advertising lines.
     *
     * @return array{balance: float, model: OtpBalance|AdvertisingBalance, credit_type: string}
     */
    public function getBalance(string $serviceType, GhorfeOnlineList $business): array
    {
        if ($serviceType === 'otp') {
            $model = OtpBalance::firstOrCreate(
                ['ghorfe_online_list_id' => $business->id],
                ['low_balance_threshold' => 100000]
            );

            return [
                'balance' => (float) $model->balance,
                'model' => $model,
                'credit_type' => 'otp',
            ];
        }

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
     * Hard block check if balance is insufficient.
     *
     * @throws InsufficientBalanceException
     */
    public function verifyBalance(string $serviceType, GhorfeOnlineList $business, float $requiredCost): array
    {
        $balanceInfo = $this->getBalance($serviceType, $business);

        if ($balanceInfo['balance'] < $requiredCost) {
            throw new InsufficientBalanceException(
                sprintf(
                    'موجودی ناکافی است. موجودی فعلی: %s ریال، هزینه پیامک: %s ریال.',
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
     * Substitute named dynamic parameters using DynamicParameterResolver.
     */
    public function resolvePlaceholders(string $rawContent, array $context = [], ?User $recipient = null): string
    {
        if ($recipient) {
            $context['user'] = $recipient;
            $context['first_name'] = $context['first_name'] ?? $recipient->name;
            $context['last_name'] = $context['last_name'] ?? $recipient->family;
        }

        return $this->dynamicParameterResolver->applyTo($rawContent, $context);
    }

    /**
     * Dispatch the message to the external gateway (IranSms or NeginSms).
     */
    public function dispatchToGateway(string $smsPanel, string $phone, string $message): void
    {
        if ($this->gatewayDispatcher !== null) {
            ($this->gatewayDispatcher)($smsPanel, $phone, $message);
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
     * The category of an OTP send: the explicit one, else the template's. It must be one of the fixed
     * SmsOtpCategory values and, when a template carries a category, agree with it.
     *
     * @param array<string, mixed> $data
     * @throws DomainException
     */
    public function resolveOtpCategory(array $data): string
    {
        $templateId = $data['sms_message_template_id'] ?? $data['template_id'] ?? null;
        $templateCategory = $templateId
            ? \App\Models\SmsMessageTemplate::whereKey($templateId)->value('otp_category')
            : null;
        $chosen = !empty($data['otp_category']) ? (string) $data['otp_category'] : null;

        $category = $chosen ?: $templateCategory;
        if (!$category || SmsOtpCategory::tryFrom($category) === null) {
            throw new DomainException('برای پیامک OTP انتخاب دسته‌بندی سیستمی (احراز هویت، فروش، مالی یا حقوقی) الزامی است.');
        }
        if ($templateCategory && $templateCategory !== $category) {
            throw new DomainException('دسته‌بندی انتخابی با دسته‌بندی الگوی OTP مطابقت ندارد.');
        }

        return $category;
    }

    /**
     * Execute the single SMS send workflow.
     *
     * @param array<string, mixed> $data
     * @param GhorfeOnlineList|null $business
     * @param User|null $creator
     * @return SmsMessage
     */
    public function send(array $data, ?GhorfeOnlineList $business = null, ?User $creator = null): SmsMessage
    {
        $business = $business ?? ghorfe();

        if (!$business) {
            throw new DomainException('کسب‌وکار معتبری برای ارسال پیامک یافت نشد.');
        }

        $creator = $creator ?? auth()->user();
        $serviceType = $data['service_type'] ?? 'advertising';

        // 0. OTP messages carry exactly one fixed system category; promotional ones never do.
        $otpCategory = $serviceType === 'otp' ? $this->resolveOtpCategory($data) : null;

        // 1. Resolve recipient user if exists
        $recipientPhone = $data['recipient_phone'];
        $recipientUser = null;
        if (!empty($data['user_id'])) {
            $recipientUser = User::find($data['user_id']);
        } elseif (!empty($recipientPhone)) {
            $recipientUser = User::where('phone', $recipientPhone)->first();
        }

        // 2. Resolve dynamic parameters in template/message
        $rawMessage = $data['message'] ?? '';
        $resolvedMessage = $this->resolvePlaceholders($rawMessage, $data['context'] ?? [], $recipientUser);

        // 3. "لغو 11" is appended unconditionally for advertising messages (see calculateSegmentation).
        $segmentation = $this->calculateSegmentation($resolvedMessage, $serviceType);
        $finalContent = $segmentation['text'];
        $pageCount = $segmentation['page_count'];
        $characterCount = $segmentation['character_count'];

        // 4. Calculate cost
        $cost = $this->calculateCost($pageCount, $segmentation['is_unicode']);

        // 5. Resolve sender number (OTP: auto-selected, strictly NO override; Advertising: business assigned)
        $requestedSenderId = !empty($data['sender_number_id']) ? (int) $data['sender_number_id'] : null;
        $senderNumber = $this->resolveSenderNumber($serviceType, $business, $requestedSenderId, $creator);

        // 6. Resolve the balance row. The authoritative sufficiency check runs under a
        //    row lock inside the transaction below (an unlocked pre-check here would race).
        $balanceInfo = $this->getBalance($serviceType, $business);

        $trackingId = 'TRK-' . strtoupper(Str::random(12));

        // 7. Atomic DB Transaction: create sms_message, deduct balance, create sms_transaction, dispatch gateway
        return DB::transaction(function () use (
            $business,
            $creator,
            $serviceType,
            $senderNumber,
            $recipientPhone,
            $recipientUser,
            $finalContent,
            $characterCount,
            $pageCount,
            $cost,
            $trackingId,
            $balanceInfo,
            $data,
            $otpCategory
        ) {
            $groupId = $data['sms_message_group_id'] ?? $data['category_id'] ?? null;
            // OTP is the only message type that can be classified (محرمانه).
            // Advertising messages can never have any classification other than regular (عادی).
            if ($serviceType !== 'otp') {
                $classification = 'regular';
            } else {
                $classification = $data['classification'] ?? $data['type'] ?? 'regular';
                if ($groupId) {
                    $group = \App\Models\SmsMessageGroup::find($groupId);
                    if ($group && $group->isConfidential()) {
                        $classification = 'classified';
                    }
                }
            }

            $smsMessage = SmsMessage::create([
                'ghorfe_online_list_id' => $business->id,
                'direction' => 'outbound',
                'service_type' => $serviceType,
                'sender_number_id' => $senderNumber->id,
                'recipient_phone' => $recipientPhone,
                'sender_phone' => $senderNumber->number,
                'sms_message_template_id' => $data['sms_message_template_id'] ?? null,
                'sms_message_group_id' => $groupId,
                'classification' => $classification,
                'otp_category' => $otpCategory,
                'content' => $finalContent,
                'character_count' => $characterCount,
                'page_count' => $pageCount,
                'status' => 'queued',
                'cost' => $cost,
                'tracking_id' => $trackingId,
                'user_id' => $recipientUser?->id,
                'request_id' => $data['request_id'] ?? null,
                'created_by' => $creator?->id,
                'sender_role' => $this->audit->resolveSenderRole($creator),
                'is_bulk' => false,
            ]);

            $this->audit->record($creator, $serviceType === 'otp' ? SmsAuditService::CHANNEL_OTP : SmsAuditService::CHANNEL_SINGLE, [
                'sms_message' => $smsMessage,
            ]);

            // Lock the balance row, verify it covers the cost, and debit atomically.
            // Throws InsufficientBalanceException, which rolls back the message insert above.
            $balanceBefore = $balanceInfo['model']->lockAndDebit($cost);
            $balanceAfter = $balanceBefore - $cost;

            // Record transaction
            SmsTransaction::create([
                'ghorfe_online_list_id' => $business->id,
                'credit_type' => $balanceInfo['credit_type'],
                'sender_number_id' => $senderNumber->id,
                'charge_amount' => 0,
                'consume_amount' => $cost,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'sms_message_id' => $smsMessage->id,
                'tracking_id' => $trackingId,
                'created_by' => $creator?->id,
            ]);

            // Legacy backward-compatibility record
            try {
                \App\Models\Groupsms::create([
                    'title' => $serviceType === 'otp' ? 'پیامک اعتبارسنجی' : ($serviceType === 'advertising' ? 'پیامک تبلیغاتی' : 'پیامک تکی'),
                    'message' => $finalContent,
                    'phones' => $recipientPhone,
                    'type' => 'single',
                    'status' => 2,
                    'flag' => true,
                    'send_at' => now(),
                    'ghorfe_online_list_id' => $business?->id,
                    'sms_message_group_id' => $groupId,
                    'classification' => $classification,
                    'sms_message_id' => $smsMessage->id,
                ]);
            } catch (\Throwable) {}

            // Dispatch through external gateway
            try {
                $this->dispatchToGateway(
                    $senderNumber->sms_panel ?? 'IranSms',
                    $recipientPhone,
                    $finalContent
                );
                $smsMessage->update(['status' => 'sent']);
            } catch (\Throwable $e) {
                Log::error('SMS Gateway Dispatch Error: ' . $e->getMessage(), [
                    'tracking_id' => $trackingId,
                    'phone' => $recipientPhone,
                ]);
                $smsMessage->update([
                    'status' => 'failed',
                    'error_reason' => $e->getMessage(),
                ]);
            }

            return $smsMessage;
        });
    }

    /**
     * Forward / refer an existing message to another contact with referrer, timestamp, and history logged.
     */
    public function forwardMessage(
        SmsMessage $originalMessage,
        string $targetPhone,
        ?int $targetUserId = null,
        ?int $referrerId = null,
        ?Carbon $timestamp = null,
        ?GhorfeOnlineList $business = null
    ): SmsMessage {
        $business = $business ?? $originalMessage->business ?? ghorfe();
        $referrer = $referrerId ? User::find($referrerId) : auth()->user();
        $timestamp = $timestamp ?? now();

        $sendData = [
            'recipient_phone' => $targetPhone,
            'user_id' => $targetUserId,
            'message' => $originalMessage->content,
            'service_type' => $originalMessage->service_type,
            'sender_number_id' => $originalMessage->sender_number_id,
            'otp_category' => $originalMessage->service_type === 'otp' ? $originalMessage->otp_category : null,
        ];

        $newMessage = $this->send($sendData, $business, $referrer);

        // Log history of referral
        $description = sprintf(
            'ارجاع پیامک شماره #%d به %s توسط %s در تاریخ %s',
            $originalMessage->id,
            $targetPhone,
            $referrer ? ($referrer->name . ' ' . $referrer->family) : 'کاربر سیستم',
            $timestamp->toDateTimeString()
        );

        if (class_exists(History::class)) {
            try {
                History::query()->create([
                    'historiable_type' => SmsMessage::class,
                    'historiable_id' => $newMessage->id,
                    'user_id' => $referrer?->id ?? 1,
                    'callcenter_id' => $referrer?->id ?? 1,
                    'status' => 2, // انتقال به همکار دیگر / ارجاع
                    'description' => $description,
                ]);
            } catch (\Throwable $e) {
                Log::warning('History log error: ' . $e->getMessage());
            }
        }

        Log::info($description, [
            'original_message_id' => $originalMessage->id,
            'new_message_id' => $newMessage->id,
            'referrer_id' => $referrer?->id,
            'target_phone' => $targetPhone,
            'timestamp' => $timestamp->toIso8601String(),
        ]);

        return $newMessage;
    }

    /**
     * Search contacts by phone or name.
     */
    public function searchContacts(string $keyword, int $limit = 20): array
    {
        $keyword = trim($keyword);
        if ($keyword === '') {
            return [];
        }

        return User::query()
            ->where(function ($query) use ($keyword) {
                $query->where('phone', 'like', "%{$keyword}%")
                    ->orWhere('name', 'like', "%{$keyword}%")
                    ->orWhere('family', 'like', "%{$keyword}%")
                    ->orWhereRaw("CONCAT(COALESCE(name, ''), ' ', COALESCE(family, '')) LIKE ?", ["%{$keyword}%"]);
            })
            ->take($limit)
            ->get(['id', 'name', 'family', 'phone'])
            ->map(fn (User $user) => [
                'id' => $user->id,
                'name' => trim(($user->name ?? '') . ' ' . ($user->family ?? '')) ?: 'بدون نام',
                'first_name' => $user->name,
                'family' => $user->family,
                'phone' => $user->phone,
            ])
            ->all();
    }

    /**
     * Create or retrieve a contact inline without leaving the page.
     */
    public function storeContact(array $data): User
    {
        $phone = trim($data['phone']);
        $name = trim($data['name'] ?? '');
        $family = trim($data['family'] ?? '');

        $user = User::where('phone', $phone)->first();

        if ($user) {
            if ($name && !$user->name) {
                $user->name = $name;
            }
            if ($family && !$user->family) {
                $user->family = $family;
            }
            $user->save();

            return $user;
        }

        return User::create([
            'name' => $name,
            'family' => $family,
            'phone' => $phone,
            'password' => Hash::make(Str::random(16)),
            'level' => 'user',
            'active' => true,
        ]);
    }
}
