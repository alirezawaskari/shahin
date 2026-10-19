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
    public function getRequiredTier(array $filters = [], string $recipientType = 'normal', ?int $ownBusinessId = null): string
    {
        // 1. Cross-business targeting requires Titanium: "all businesses", or — when the
        //    sender's own business is known — any business other than their own.
        $businessIds = $this->businessFilterIds($filters);
        if (in_array('all', $businessIds, true)) {
            return self::TIER_TITANIUM;
        }
        if ($ownBusinessId !== null) {
            foreach ($businessIds as $id) {
                if ((string) $id !== (string) $ownBusinessId) {
                    return self::TIER_TITANIUM;
                }
            }
        }

        // 1b. Organization Database Access requires Titanium
        foreach (['organization_id', 'organization'] as $orgKey) {
            $val = $filters[$orgKey] ?? null;
            if ($val !== null && $val !== '' && $val !== 'all') {
                return self::TIER_TITANIUM;
            }
        }

        // 2. Advertising recipient pool requires Diamond
        if (strtolower($recipientType) === 'advertising') {
            return self::TIER_DIAMOND;
        }

        // 2b. Requests-page audience (Customer CRM):
        if (!empty($filters['request_filter']) && is_array($filters['request_filter'])) {
            // Organization inside request_filter requires Titanium. The representation filter only
            // exists on the requests page's own «ارسال پیامک» (__direct); the audience picker has none.
            $orgKeys = ['organization', 'organization_id'];
            if (!empty($filters['request_filter']['__direct'])) {
                $orgKeys = array_merge($orgKeys, ['representation', 'representation_id']);
            }
            foreach ($orgKeys as $orgKey) {
                $val = $filters['request_filter'][$orgKey] ?? null;
                if ($val !== null && $val !== '' && $val !== 'all') {
                    return self::TIER_TITANIUM;
                }
            }

            // Category / service filters inside request_filter require Diamond
            foreach (['product_category', 'product', 'product_attribute', 'service_category', 'sub_service_category', 'service_items', 'service'] as $key) {
                $value = $filters['request_filter'][$key] ?? null;
                $value = array_filter((array) $value, fn ($v) => $v !== null && $v !== '' && $v !== 'all');
                if (!empty($value)) {
                    return self::TIER_DIAMOND;
                }
            }

            // Base clinic requests / customer CRM is permitted on Silver
            return self::TIER_SILVER;
        }

        // 3. Service or Product category filters require Diamond (Service Provider Database Access)
        if (!empty($filters['service_category_id']) || !empty($filters['product_category_id']) || !empty($filters['service_id'])) {
            return self::TIER_DIAMOND;
        }

        // 4. User Database Access requires Gold (Silver has NO access to user database)
        $userFilterKeys = [
            'role', 'level', 'birth_year', 'birth_month', 'birth_day',
            'gender', 'sex', 'province_id', 'city_id',
            'start_date', 'startDate', 'end_date', 'endDate'
        ];
        foreach ($userFilterKeys as $uKey) {
            $uVal = $filters[$uKey] ?? null;
            if ($uVal !== null && $uVal !== '' && $uVal !== 'all') {
                return self::TIER_GOLD;
            }
        }

        // 5. Default/basic (own clinic customer requests, manual custom numbers, Excel uploads) require Silver
        return self::TIER_SILVER;
    }

    /**
     * Requests-page audience filter fields (request_filter[...]) => minimum tier needed to use them.
     * Mirrors getRequiredTier(): category / product / service filters need Diamond, organization /
     * representation / business need Titanium, everything else is basic (Silver). The server still
     * enforces via getRequiredTier(); this map only drives which fields the UI renders.
     *
     * @return array<string, string>
     */
    public function requestFilterFieldTiers(): array
    {
        $silver = ['name', 'family', 'user_phone', 'description', 'startDate', 'endDate', 'sex', 'has_factor',
            'type', 'request_type', 'status', 'province', 'city', 'users', 'agents'];
        $diamond = ['product_category', 'service_category', 'sub_service_category', 'service_items', 'product', 'product_attribute'];
        $titanium = ['organization', 'representation', 'business'];

        $map = [];
        foreach ($silver as $k) {
            $map[$k] = self::TIER_SILVER;
        }
        foreach ($diamond as $k) {
            $map[$k] = self::TIER_DIAMOND;
        }
        foreach ($titanium as $k) {
            $map[$k] = self::TIER_TITANIUM;
        }

        return $map;
    }

    /**
     * Field keys of requestFilterFieldTiers() the user's effective tier may use.
     *
     * @return array<int, string>
     */
    public function requestFilterFieldsFor(?User $user): array
    {
        $rank = $this->getTierRank($this->effectiveTier($user));

        return array_keys(array_filter(
            $this->requestFilterFieldTiers(),
            fn (string $tier) => $rank >= $this->getTierRank($tier)
        ));
    }

    /**
     * The business ids a filter set targets, from either key, as a list of strings
     * (a scalar, a list, or the literal "all").
     *
     * @return array<int, string>
     */
    public function businessFilterIds(array $filters): array
    {
        $raw = $filters['business'] ?? $filters['ghorfe_online_list_id'] ?? null;

        return array_values(array_map(
            'strval',
            array_filter((array) $raw, fn ($v) => is_scalar($v) && $v !== '')
        ));
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
     * The permission that unlocks recipient phone numbers (bulk preview samples) and the
     * «ارسال پیامک» button of the requests page.
     */
    public const PERMISSION_VIEW_PHONE_NUMBERS = 'view-phone-numbers';

    /**
     * Whether the user may see recipient phone numbers / use Direct SMS (superAdmin always).
     */
    public function canViewPhoneNumbers(?User $user): bool
    {
        return $user && ($user->isSuperAdmin() || $user->isAbleTo(self::PERMISSION_VIEW_PHONE_NUMBERS));
    }

    /**
     * OTP send/receive, OTP credit and OTP reports are internal-only: Titanium (superAdmin
     * is always Titanium). Lower tiers get no access to any of it.
     */
    public function canUseOtp(?User $user): bool
    {
        return $user && $this->getTierRank($this->effectiveTier($user)) >= self::TIER_RANKS[self::TIER_TITANIUM];
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
    public function assertCanQuery(User $user, array $filters = [], string $recipientType = 'normal', ?int $ownBusinessId = null): void
    {
        if ($user->isSuperAdmin()) {
            return;
        }

        $requiredTier = $this->getRequiredTier($filters, $recipientType, $ownBusinessId);
        $userTier = $user->access_tier ?: self::TIER_SILVER;

        if (!$this->canAccess($user, $requiredTier)) {
            $reason = match ($requiredTier) {
                self::TIER_TITANIUM => 'دسترسی به بانک سازمان‌ها، ارگان‌ها و نمایندگی‌ها یا کل کسب‌وکارها نیازمند سطح دسترسی تیتانیوم (Titanium) است.',
                self::TIER_DIAMOND => strtolower($recipientType) === 'advertising'
                    ? 'ارسال پیامک به مخاطبین تبلیغاتی نیازمند سطح دسترسی الماس (Diamond) یا بالاتر است.'
                    : 'فیلتر بر اساس دسته‌بندی خدمات، کالاها و خدمت‌دهندگان نیازمند سطح دسترسی الماس (Diamond) یا بالاتر است.',
                self::TIER_GOLD => 'دسترسی به بانک کاربران سامانه نیازمند سطح دسترسی طلایی (Gold) یا بالاتر است. کاربران سطح نقره‌ای صرفاً می‌توانند به پرونده‌ها و مشتریان کلینیک خود پیامک ارسال کنند.',
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
