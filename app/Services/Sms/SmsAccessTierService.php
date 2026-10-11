<?php

namespace App\Services\Sms;

use App\Exceptions\Sms\TierAccessDeniedException;
use App\Models\User;

class SmsAccessTierService
{
    public const TIER_SILVER = 'silver';
    public const TIER_GOLD = 'gold';
    public const TIER_DIAMOND = 'diamond';
    public const TIER_TITANIUM = 'titanium';

    public const TIER_RANKS = [
        self::TIER_SILVER => 1,
        self::TIER_GOLD => 2,
        self::TIER_DIAMOND => 3,
        self::TIER_TITANIUM => 4,
    ];

    /**
     * Get numeric rank for a given tier string.
     */
    public function getTierRank(?string $tier): int
    {
        $tier = strtolower($tier ?? self::TIER_SILVER);
        return self::TIER_RANKS[$tier] ?? 1;
    }

    /**
     * Determine the minimum tier required to query a specific recipient configuration.
     *
     * @param array<string, mixed> $filters
     * @param string $recipientType
     * @return string
     */
    public function getRequiredTier(array $filters = [], string $recipientType = 'normal'): string
    {
        // 1. Cross-business targeting requires Titanium
        $businessFilter = $filters['business'] ?? $filters['ghorfe_online_list_id'] ?? null;
        if (!empty($businessFilter) && ($businessFilter === 'all' || (is_array($businessFilter) && in_array('all', $businessFilter, true)))) {
            return self::TIER_TITANIUM;
        }

        // 2. Advertising recipient pool requires Diamond
        if (strtolower($recipientType) === 'advertising') {
            return self::TIER_DIAMOND;
        }

        // 3. Service or Product category filters require Diamond
        if (!empty($filters['service_category_id']) || !empty($filters['product_category_id']) || !empty($filters['service_id'])) {
            return self::TIER_DIAMOND;
        }

        // 4. Role, Birth date/year, or special filters require Gold
        if (!empty($filters['role']) || !empty($filters['birth_year']) || !empty($filters['birth_month']) || !empty($filters['birth_day']) || !empty($filters['level'])) {
            return self::TIER_GOLD;
        }

        // 5. Default/basic filters (gender, date range, own business customers) require Silver
        return self::TIER_SILVER;
    }

    /**
     * The tier actually in effect for a user. superAdmin is always Titanium,
     * regardless of the raw access_tier column. Use this for display and UI gating;
     * the raw column is only read for ordinary users.
     */
    public function effectiveTier(?User $user): string
    {
        if (!$user) {
            return self::TIER_SILVER;
        }

        if ($user->isSuperAdmin()) {
            return self::TIER_TITANIUM;
        }

        return $user->access_tier ?: self::TIER_SILVER;
    }

    /**
     * Check if a user's tier satisfies the required tier.
     */
    public function canAccess(User $user, string $requiredTier): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        $userTier = $user->access_tier ?: self::TIER_SILVER;
        return $this->getTierRank($userTier) >= $this->getTierRank($requiredTier);
    }

    /**
     * Assert that the user is authorized to execute the query, throwing TierAccessDeniedException if not.
     *
     * @throws TierAccessDeniedException
     */
    public function assertCanQuery(User $user, array $filters = [], string $recipientType = 'normal'): void
    {
        if ($user->isSuperAdmin()) {
            return;
        }

        $requiredTier = $this->getRequiredTier($filters, $recipientType);
        $userTier = $user->access_tier ?: self::TIER_SILVER;

        if (!$this->canAccess($user, $requiredTier)) {
            $reason = match ($requiredTier) {
                self::TIER_TITANIUM => 'جستجو در کل کسب‌وکارها و بانک شماره‌های عمومی نیازمند سطح دسترسی تیتانیوم (Titanium) است.',
                self::TIER_DIAMOND => strtolower($recipientType) === 'advertising'
                    ? 'ارسال پیامک به مخاطبین تبلیغاتی نیازمند سطح دسترسی الماس (Diamond) یا بالاتر است.'
                    : 'فیلتر بر اساس دسته‌بندی خدمات یا محصولات نیازمند سطح دسترسی الماس (Diamond) یا بالاتر است.',
                self::TIER_GOLD => 'استفاده از فیلترهای پیشرفته (مانند نقش، سطح، یا تاریخ تولد) نیازمند سطح دسترسی طلایی (Gold) یا بالاتر است.',
                default => 'سطح دسترسی شما برای این عملیات کافی نمی‌باشد.',
            };

            throw new TierAccessDeniedException(
                sprintf('%s سطح دسترسی شما: %s، سطح مورد نیاز: %s.', $reason, $userTier, $requiredTier),
                $requiredTier,
                $userTier
            );
        }
    }
}
