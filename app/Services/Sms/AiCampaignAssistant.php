<?php

namespace App\Services\Sms;

use App\Contracts\Ai\LlmClientInterface;
use App\Exceptions\Sms\TierAccessDeniedException;
use App\Models\AiSetting;
use App\Models\GhorfeOnlineList;
use App\Models\SenderNumber;
use App\Models\User;
use DomainException;
use InvalidArgumentException;

class AiCampaignAssistant
{
    /**
     * Whitelist of valid recipient filter keys supported by BulkSmsRecipientFilterService.
     */
    public const ALLOWED_FILTER_KEYS = [
        'business',
        'ghorfe_online_list_id',
        'role',
        'level',
        'gender',
        'sex',
        'birth_year',
        'birth_month',
        'birth_day',
        'start_date',
        'end_date',
        'province_id',
        'city_id',
        'service_id',
        'service_category_id',
        'product_category_id',
        'organization_id',
        'representation_id',
        'custom_numbers',
    ];

    public function __construct(
        protected LlmClientInterface $llmClient,
        protected BulkSmsRecipientFilterService $recipientFilterService,
        protected BulkSmsSendService $bulkSmsSendService,
        protected SmsAccessTierService $accessTierService
    ) {
    }

    /**
     * Get the LLM client instance (useful for tests and inspection).
     */
    public function getLlmClient(): LlmClientInterface
    {
        return $this->llmClient;
    }

    /**
     * Resolve the active AI model name from settings or fallback.
     */
    public function resolveAiModel(): string
    {
        if (class_exists(AiSetting::class)) {
            $setting = AiSetting::first();
            if ($setting && !empty($setting->model)) {
                return $setting->model;
            }
        }

        return 'google/gemini-2.5-pro-preview';
    }

    /**
     * Sanitize and normalize proposed audience filters against BulkSmsRecipientFilterService schema.
     *
     * @param array<string, mixed> $rawFilters
     * @param GhorfeOnlineList|null $business
     * @return array<string, mixed>
     */
    public function sanitizeFilters(array $rawFilters, ?GhorfeOnlineList $business = null): array
    {
        $sanitized = [];

        foreach ($rawFilters as $key => $value) {
            if (!in_array($key, self::ALLOWED_FILTER_KEYS, true)) {
                continue;
            }

            if ($value === null || $value === '' || $value === 'all') {
                continue;
            }

            // Normalize sex/gender alias
            if ($key === 'sex') {
                $sanitized['gender'] = (int) $value;
                continue;
            }

            if (in_array($key, ['gender', 'province_id', 'city_id', 'service_id', 'service_category_id', 'product_category_id', 'organization_id', 'representation_id', 'birth_year', 'birth_month', 'birth_day'], true)) {
                $sanitized[$key] = is_numeric($value) ? (int) $value : $value;
                continue;
            }

            $sanitized[$key] = $value;
        }

        // Default business scope if not specified or not cross-business
        if (!isset($sanitized['business']) && !isset($sanitized['ghorfe_online_list_id']) && $business) {
            $sanitized['business'] = $business->id;
        }

        return $sanitized;
    }

    /**
     * Step 1 & 2: User enters natural language goal -> AI proposes target audience filters + count + rationale.
     *
     * @param string $goalPrompt
     * @param User $actor
     * @param GhorfeOnlineList $business
     * @param array<string, mixed> $options
     * @return array{
     *     goal: string,
     *     filters: array<string, mixed>,
     *     rationale: string,
     *     recipient_count: int,
     *     total_candidates: int,
     *     duplicate_count: int,
     *     invalid_count: int,
     *     required_tier: string,
     *     user_tier: string,
     *     is_tier_allowed: bool,
     *     tier_message: ?string
     * }
     */
    public function proposeAudience(
        string $goalPrompt,
        User $actor,
        GhorfeOnlineList $business,
        array $options = []
    ): array {
        $goalPrompt = trim($goalPrompt);
        if ($goalPrompt === '') {
            throw new InvalidArgumentException('هدف کمپین نمی‌تواند خالی باشد.');
        }

        $model = $options['model'] ?? $this->resolveAiModel();
        $recipientType = $options['recipient_type'] ?? 'normal';

        $systemInstruction = "شما یک دستیار هوشمند تارگتینگ و بازاریابی پیامکی برای کسب‌وکار '{$business->title}' هستید. "
            . "بر اساس هدف کمپین کاربر، بهترین فیلتر مخاطبان را با استفاده از فیلدهای زیر در قالب JSON برگردانید:\n"
            . "- gender: 1 (مرد) یا 2 (زن)\n"
            . "- province_id: شناسه استان (مثلا 8 برای تهران)\n"
            . "- city_id: شناسه شهر\n"
            . "- service_category_id: شناسه دسته‌بندی خدمات\n"
            . "- role: نام یا شناسه نقش\n"
            . "- level: سطح کاربر\n"
            . "- start_date / end_date: بازه تاریخ عضویت\n\n"
            . "پاسخ باید دقیقاً یک شیء JSON با دو فیلد 'filters' (شیء فیلترها) و 'rationale' (توضیح کوتاه به زبان فارسی در مورد علت انتخاب) باشد.";

        $prompt = "هدف کمپین: {$goalPrompt}\nکسب‌وکار: {$business->title}\nلطفاً فیلترهای مناسب و علت انتخاب را خروجی دهید.";

        $aiResponse = $this->llmClient->generateJson($prompt, [
            'system' => $systemInstruction,
            'model' => $model,
            'goal' => $goalPrompt,
        ]);

        $rawFilters = (array) ($aiResponse['filters'] ?? []);
        $rationale = (string) ($aiResponse['rationale'] ?? 'مخاطبان بر اساس هدف کمپین شما توسط هوش مصنوعی انتخاب شدند.');

        $cleanFilters = $this->sanitizeFilters($rawFilters, $business);

        // Access tier evaluation
        $requiredTier = $this->accessTierService->getRequiredTier($cleanFilters, $recipientType);
        $userTier = $this->accessTierService->effectiveTier($actor);
        $isTierAllowed = $this->accessTierService->canAccess($actor, $requiredTier);

        $filterResult = [
            'valid_count' => 0,
            'total_candidates' => 0,
            'duplicate_count' => 0,
            'invalid_count' => 0,
        ];
        $tierMessage = null;

        if ($isTierAllowed) {
            $filterResult = $this->recipientFilterService->filterRecipients($cleanFilters, $actor, $business, $recipientType);
        } else {
            $tierMessage = sprintf(
                'فیلترهای پیشنهادی نیازمند سطح دسترسی %s هستند (سطح شما: %s).',
                $requiredTier,
                $userTier
            );
        }

        return [
            'goal' => $goalPrompt,
            'filters' => $cleanFilters,
            'rationale' => $rationale,
            'recipient_count' => $filterResult['valid_count'] ?? 0,
            'total_candidates' => $filterResult['total_candidates'] ?? 0,
            'duplicate_count' => $filterResult['duplicate_count'] ?? 0,
            'invalid_count' => $filterResult['invalid_count'] ?? 0,
            'required_tier' => $requiredTier,
            'user_tier' => $userTier,
            'is_tier_allowed' => $isTierAllowed,
            'tier_message' => $tierMessage,
        ];
    }

    /**
     * Step 4: AI drafts message text sized to the audience and page-budget.
     * Note: The user MUST have approved the audience before calling this step.
     *
     * @param string $goalPrompt
     * @param array<string, mixed> $approvedAudience Summary or filters of approved audience
     * @param int $pageBudget Maximum desired SMS pages (1, 2, or 3)
     * @param User|null $actor
     * @param GhorfeOnlineList|null $business
     * @param array<string, mixed> $options
     * @return array{
     *     goal: string,
     *     draft_text: string,
     *     page_budget: int,
     *     character_count: int,
     *     page_count: int,
     *     cost_per_message: float,
     *     is_unicode: bool,
     *     fits_page_budget: bool
     * }
     */
    public function draftMessage(
        string $goalPrompt,
        array $approvedAudience,
        int $pageBudget = 1,
        ?User $actor = null,
        ?GhorfeOnlineList $business = null,
        array $options = []
    ): array {
        $goalPrompt = trim($goalPrompt);
        if ($goalPrompt === '') {
            throw new InvalidArgumentException('هدف کمپین نمی‌تواند خالی باشد.');
        }

        $pageBudget = max(1, min(4, $pageBudget));
        $model = $options['model'] ?? $this->resolveAiModel();
        $businessName = $business?->title ?? 'کسب‌وکار';

        $maxChars = match ($pageBudget) {
            1 => 60,  // Leave headroom for "لغو 11"
            2 => 125,
            3 => 190,
            default => 250,
        };

        $audienceRationale = $approvedAudience['rationale'] ?? '';
        $recipientCount = $approvedAudience['recipient_count'] ?? $approvedAudience['count'] ?? 0;

        $systemInstruction = "شما یک نویسنده حرفه‌ای متون تبلیغاتی و پیامکی در ایران هستید. "
            . "متن پیامکی بنویسید که جذاب، متقاعدکننده و کاملاً متناسب با جامعه هدف و بودجه کاراکتری باشد.\n"
            . "محدودیت سخت: حداکثر طول متن باید حدود {$maxChars} کاراکتر فارسی باشد تا در سقف {$pageBudget} صفحه پیامک جا شود.\n"
            . "فقط و فقط متن نهایی پیامک را بدون هیچ توضیح اضافی، بدون گیومه و بدون علائم نامربوط برگردانید.";

        $prompt = "نام کسب‌وکار: {$businessName}\n"
            . "هدف کمپین: {$goalPrompt}\n"
            . "جامعه هدف ({$recipientCount} نفر): {$audienceRationale}\n"
            . "سقف صفحه: {$pageBudget} صفحه (حدود {$maxChars} کاراکتر)";

        $draftText = $this->llmClient->generateText($prompt, [
            'system' => $systemInstruction,
            'model' => $model,
            'page_budget' => $pageBudget,
            'goal' => $goalPrompt,
        ]);

        $draftText = trim($draftText, " \t\n\r\0\x0B\"'«»");

        // "لغو 11" is appended unconditionally for advertising messages.
        $segmentation = $this->bulkSmsSendService->calculateMessageSegmentation($draftText);

        return [
            'goal' => $goalPrompt,
            'draft_text' => $draftText,
            'page_budget' => $pageBudget,
            'character_count' => $segmentation['character_count'],
            'page_count' => $segmentation['page_count'],
            'cost_per_message' => $segmentation['cost_per_message'],
            'is_unicode' => $segmentation['is_unicode'],
            'fits_page_budget' => ($segmentation['page_count'] <= $pageBudget),
        ];
    }

    /**
     * Step 6: Final cost calculation (reusing bulk-send cost logic).
     *
     * @param string $messageText
     * @param array<string, mixed> $filters
     * @param GhorfeOnlineList $business
     * @param User $actor
     * @param int|null $senderNumberId
     * @param string $recipientType
     * @return array<string, mixed>
     */
    public function calculateCost(
        string $messageText,
        array $filters,
        GhorfeOnlineList $business,
        User $actor,
        ?int $senderNumberId = null,
        string $recipientType = 'normal'
    ): array {
        // 1. Segmentation & Per-Message Cost ("لغو 11" appended unconditionally for advertising)
        $segmentation = $this->bulkSmsSendService->calculateMessageSegmentation($messageText);
        $pageCount = $segmentation['page_count'];
        $tariff = $segmentation['tariff'];

        // 2. Filter & Deduplicate Recipients
        $filterResult = $this->recipientFilterService->filterRecipients($filters, $actor, $business, $recipientType);
        $validCount = $filterResult['valid_count'];

        // 3. Campaign Total Cost
        $totalCost = $this->bulkSmsSendService->calculateCampaignCost($validCount, $pageCount);

        // 4. Resolve Advertising Sender Number & Balance
        $currentBalance = 0.0;
        $senderNumber = null;

        try {
            $senderNumber = $this->bulkSmsSendService->resolveAdvertisingSenderNumber($business, $senderNumberId);
            $balanceInfo = $this->bulkSmsSendService->getBalance($business);
            $currentBalance = $balanceInfo['balance'];
        } catch (\Throwable) {
            // Unassigned or missing sender number
        }

        $hasSufficientBalance = ($currentBalance >= $totalCost) && ($totalCost > 0);

        return [
            'recipient_summary' => [
                'total_candidates' => $filterResult['total_candidates'],
                'valid_count' => $filterResult['valid_count'],
                'invalid_count' => $filterResult['invalid_count'],
                'duplicate_count' => $filterResult['duplicate_count'],
                'required_tier' => $filterResult['required_tier'],
            ],
            'message_summary' => [
                'raw_text' => $messageText,
                'resolved_text' => $segmentation['text'],
                'character_count' => $segmentation['character_count'],
                'page_count' => $segmentation['page_count'],
                'is_unicode' => $segmentation['is_unicode'],
                'cost_per_message' => $segmentation['cost_per_message'],
                'tariff' => $tariff,
            ],
            'financial_summary' => [
                'estimated_total_cost' => $totalCost,
                'current_balance' => $currentBalance,
                'balance_after' => max(0, $currentBalance - $totalCost),
                'has_sufficient_balance' => $hasSufficientBalance,
                'balance_deficit' => max(0, $totalCost - $currentBalance),
                'sender_number' => $senderNumber ? [
                    'id' => $senderNumber->id,
                    'number' => $senderNumber->number,
                ] : null,
            ],
        ];
    }
}
