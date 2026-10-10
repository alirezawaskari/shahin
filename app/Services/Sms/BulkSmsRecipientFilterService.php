<?php

namespace App\Services\Sms;

use App\Helpers\FilterHelper;
use App\Helpers\Helper;
use App\Models\GhorfeOnlineList;
use App\Models\User;
use DomainException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class BulkSmsRecipientFilterService
{
    public function __construct(
        protected SmsAccessTierService $accessTierService
    ) {
    }

    /**
     * Normalize an Iranian phone number into the standard 11-digit 09xxxxxxxxx format.
     * Returns null if the phone is structurally invalid.
     */
    public function normalizePhoneNumber(?string $phone): ?string
    {
        if ($phone === null) {
            return null;
        }

        // 1. Convert Persian/Arabic digits to English
        if (class_exists(Helper::class) && method_exists(Helper::class, 'numberConverter')) {
            $phone = Helper::numberConverter($phone);
        } else {
            $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹', '٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
            $english = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
            $phone = str_replace($persian, $english, $phone);
        }

        // 2. Strip non-digits
        $cleaned = preg_replace('/[^0-9]/', '', (string) $phone);

        if (!$cleaned) {
            return null;
        }

        // 3. Normalize international prefixes
        // 00989xxxxxxxxx (14 digits) -> 09xxxxxxxxx
        if (str_starts_with($cleaned, '00989') && strlen($cleaned) === 14) {
            $cleaned = '0' . substr($cleaned, 4);
        }
        // 989xxxxxxxxx (12 digits) -> 09xxxxxxxxx
        elseif (str_starts_with($cleaned, '989') && strlen($cleaned) === 12) {
            $cleaned = '0' . substr($cleaned, 2);
        }
        // 9xxxxxxxxx (10 digits) -> 09xxxxxxxxx
        elseif (str_starts_with($cleaned, '9') && strlen($cleaned) === 10) {
            $cleaned = '0' . $cleaned;
        }

        // 4. Validate exact 11-digit Iranian mobile pattern: 09[0-9]{9}
        if (preg_match('/^09[0-9]{9}$/', $cleaned)) {
            return $cleaned;
        }

        return null;
    }

    /**
     * Validate and de-duplicate an iterable list of candidate recipients.
     *
     * @param iterable<array{phone: ?string, user_id?: ?int, name?: ?string}> $candidates
     * @return array{
     *     total_candidates: int,
     *     valid_count: int,
     *     invalid_count: int,
     *     duplicate_count: int,
     *     valid_recipients: array<int, array{phone: string, user_id: ?int, name: ?string}>,
     *     invalid_recipients: array<int, array{phone: string, reason: string}>,
     *     duplicate_recipients: array<int, array{phone: string}>
     * }
     */
    public function processRecipients(iterable $candidates): array
    {
        $totalCandidates = 0;
        $validRecipients = [];
        $invalidRecipients = [];
        $duplicateRecipients = [];
        $seenPhones = [];

        foreach ($candidates as $candidate) {
            $totalCandidates++;
            $rawPhone = trim((string) ($candidate['phone'] ?? ''));

            if ($rawPhone === '') {
                $invalidRecipients[] = [
                    'phone' => '(خالی)',
                    'reason' => 'شماره تلفن خالی است.',
                ];
                continue;
            }

            $normalized = $this->normalizePhoneNumber($rawPhone);

            if ($normalized === null) {
                $invalidRecipients[] = [
                    'phone' => $rawPhone,
                    'reason' => 'فرمت شماره موبایل نامعتبر است (الگوی صحیح: 09xxxxxxxxx).',
                ];
                continue;
            }

            if (isset($seenPhones[$normalized])) {
                $duplicateRecipients[] = [
                    'phone' => $normalized,
                ];
                continue;
            }

            $seenPhones[$normalized] = true;
            $validRecipients[] = [
                'phone' => $normalized,
                'user_id' => $candidate['user_id'] ?? null,
                'name' => $candidate['name'] ?? null,
            ];
        }

        return [
            'total_candidates' => $totalCandidates,
            'valid_count' => count($validRecipients),
            'invalid_count' => count($invalidRecipients),
            'duplicate_count' => count($duplicateRecipients),
            'valid_recipients' => $validRecipients,
            'invalid_recipients' => $invalidRecipients,
            'duplicate_recipients' => $duplicateRecipients,
        ];
    }

    /**
     * Build the query for recipient users adhering to filter specifications and access-tier gate.
     *
     * @param array<string, mixed> $filters
     * @param User $actor
     * @param GhorfeOnlineList|null $business
     * @param string $recipientType 'normal' | 'advertising'
     * @return Builder
     */
    public function buildQuery(array $filters, User $actor, ?GhorfeOnlineList $business = null, string $recipientType = 'normal'): Builder
    {
        // Enforce Access Tier Gate
        $this->accessTierService->assertCanQuery($actor, $filters, $recipientType);

        $query = User::query();

        // 1. Business scoping
        $businessId = $filters['business'] ?? $filters['ghorfe_online_list_id'] ?? null;
        if (!empty($businessId) && $businessId !== 'all') {
            $query->whereHas('ghorfeOnlineLists', function ($q) use ($businessId) {
                $q->where('ghorfe_online_lists.id', $businessId);
            });
        } elseif ($businessId === 'all') {
            // Titanium or superAdmin allowed to query across all businesses
        } elseif ($business) {
            $query->whereHas('ghorfeOnlineLists', function ($q) use ($business) {
                $q->where('ghorfe_online_lists.id', $business->id);
            });
        } else {
            // Never build a cross-business query by accident: no business context and no
            // explicit 'all' means there is no tenant to scope to.
            throw new DomainException('کسب‌وکار معتبری برای اعمال فیلتر مخاطبان یافت نشد.');
        }

        // 2. Role filter
        if (!empty($filters['role']) && $filters['role'] !== 'all') {
            $role = $filters['role'];
            $query->whereHas('roles', function ($q) use ($role) {
                if (is_numeric($role)) {
                    $q->where('roles.id', $role);
                } else {
                    $q->where('roles.name', $role);
                }
            });
        }

        // 3. Level filter
        if (!empty($filters['level']) && $filters['level'] !== 'all') {
            $query->where('level', $filters['level']);
        }

        // 4. Gender / Sex filter
        if (isset($filters['gender']) && $filters['gender'] !== '' && $filters['gender'] !== 'all') {
            $query->where('sex', $filters['gender']);
        } elseif (isset($filters['sex']) && $filters['sex'] !== '' && $filters['sex'] !== 'all') {
            $query->where('sex', $filters['sex']);
        }

        // 5. Birth Date / Year filter
        if (!empty($filters['birth_year']) && $filters['birth_year'] !== 'all') {
            $query->where('birth_year', $filters['birth_year']);
        }
        if (!empty($filters['birth_month']) && $filters['birth_month'] !== 'all') {
            $query->where('birth_month', $filters['birth_month']);
        }
        if (!empty($filters['birth_day']) && $filters['birth_day'] !== 'all') {
            $query->where('birth_day', $filters['birth_day']);
        }

        // 6. Registration Date Range
        $startDate = $filters['start_date'] ?? $filters['startDate'] ?? null;
        $endDate = $filters['end_date'] ?? $filters['endDate'] ?? null;
        if (!empty($startDate)) {
            $query->whereDate('created_at', '>=', $startDate);
        }
        if (!empty($endDate)) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        // 7. Location (Province / City)
        if (!empty($filters['province_id']) && $filters['province_id'] !== 'all') {
            $query->where('province_id', $filters['province_id']);
        }
        if (!empty($filters['city_id']) && $filters['city_id'] !== 'all') {
            $query->where('city_id', $filters['city_id']);
        }

        // 8. Service / Service Category filter
        if (!empty($filters['service_id']) && $filters['service_id'] !== 'all') {
            $serviceId = $filters['service_id'];
            $query->where(function ($q) use ($serviceId) {
                $q->where('service_id', $serviceId)
                    ->orWhereHas('factors.factorItems', function ($sq) use ($serviceId) {
                        $sq->where('service_id', $serviceId);
                    });
            });
        }
        if (!empty($filters['service_category_id']) && $filters['service_category_id'] !== 'all') {
            $catId = $filters['service_category_id'];
            $query->where(function ($q) use ($catId) {
                $q->whereHas('service.category', function ($sq) use ($catId) {
                    $sq->where('id', $catId);
                })->orWhereHas('factors.factorItems.service.category', function ($sq) use ($catId) {
                    $sq->where('id', $catId);
                });
            });
        }

        // 9. Product Category filter
        if (!empty($filters['product_category_id']) && $filters['product_category_id'] !== 'all') {
            $prodCatId = $filters['product_category_id'];
            $query->whereHas('factors.factoritemproducts.product.categories', function ($sq) use ($prodCatId) {
                $sq->where('categories.id', $prodCatId);
            });
        }

        // 10. Organization / Representation filters
        if (!empty($filters['organization_id']) && $filters['organization_id'] !== 'all') {
            $query->where('organization_id', $filters['organization_id']);
        }
        if (!empty($filters['representation_id']) && $filters['representation_id'] !== 'all') {
            $query->where('representation_id', $filters['representation_id']);
        }

        return $query;
    }

    /**
     * Filter and resolve candidates into deduplicated, valid recipient list.
     *
     * @param array<string, mixed> $filters
     * @param User $actor
     * @param GhorfeOnlineList|null $business
     * @param string $recipientType
     * @return array{
     *     total_candidates: int,
     *     valid_count: int,
     *     invalid_count: int,
     *     duplicate_count: int,
     *     valid_recipients: array<int, array{phone: string, user_id: ?int, name: ?string}>,
     *     invalid_recipients: array<int, array{phone: string, reason: string}>,
     *     duplicate_recipients: array<int, array{phone: string}>,
     *     required_tier: string
     * }
     */
    public function filterRecipients(array $filters, User $actor, ?GhorfeOnlineList $business = null, string $recipientType = 'normal'): array
    {
        $requiredTier = $this->accessTierService->getRequiredTier($filters, $recipientType);

        // Fetch candidates from DB query
        $query = $this->buildQuery($filters, $actor, $business, $recipientType);
        $users = $query->get(['id', 'name', 'family', 'phone']);

        $candidates = [];
        foreach ($users as $user) {
            $candidates[] = [
                'phone' => $user->phone,
                'user_id' => $user->id,
                'name' => trim(($user->name ?? '') . ' ' . ($user->family ?? '')) ?: 'کاربر',
            ];
        }

        // Support manual / pasted custom numbers if present
        if (!empty($filters['custom_numbers'])) {
            $manualNumbers = is_array($filters['custom_numbers'])
                ? $filters['custom_numbers']
                : preg_split('/[\r\n,;]+/', (string) $filters['custom_numbers']);

            foreach ($manualNumbers as $mNum) {
                $trimmed = trim((string) $mNum);
                if ($trimmed !== '') {
                    $candidates[] = [
                        'phone' => $trimmed,
                        'user_id' => null,
                        'name' => 'مخاطب دستی',
                    ];
                }
            }
        }

        $result = $this->processRecipients($candidates);
        $result['required_tier'] = $requiredTier;

        return $result;
    }
}
