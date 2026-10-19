<?php

namespace App\Services\Sms;

use App\Models\GhorfeOnlineList;
use App\Models\SmsMessage;
use Carbon\Carbon;
use Hekmatinasser\Verta\Verta;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class SmsLogService
{
    /**
     * Build the query for SMS messages with all filters applied.
     *
     * @param array<string, mixed> $filters
     * @param GhorfeOnlineList|null $business
     * @param bool $isSuperAdmin
     * @param bool $canUseOtp Whether the viewer may see OTP rows (SmsAccessTierService::canUseOtp()).
     * @return Builder
     */
    public function buildQuery(array $filters = [], ?GhorfeOnlineList $business = null, bool $isSuperAdmin = false, bool $canUseOtp = true): Builder
    {
        $query = SmsMessage::query()
            ->with(['business', 'senderNumber', 'template', 'group', 'campaign', 'user', 'creator', 'request']);

        // 1. Business scoping
        if (!$isSuperAdmin) {
            if ($business) {
                $query->where('ghorfe_online_list_id', $business->id);
            }
        } else {
            if (!empty($filters['business_id']) && $filters['business_id'] !== 'all') {
                $query->where('ghorfe_online_list_id', (int) $filters['business_id']);
            } elseif (!empty($filters['ghorfe_online_list_id']) && $filters['ghorfe_online_list_id'] !== 'all') {
                $query->where('ghorfe_online_list_id', (int) $filters['ghorfe_online_list_id']);
            }
        }

        // 2. Service type (otp, advertising, normal)
        if (!empty($filters['service_type']) && $filters['service_type'] !== 'all') {
            $query->where('service_type', $filters['service_type']);
        }

        // 2b. OTP rows are hidden from viewers who cannot use OTP, whatever filter was requested.
        if (!$canUseOtp) {
            $query->where(fn (Builder $q) => $q->whereNull('service_type')->orWhere('service_type', '!=', 'otp'));
        }

        // 2c. Fixed OTP system category filter: staff only (lower tiers never see OTP rows at all).
        if ($canUseOtp && !empty($filters['otp_category']) && $filters['otp_category'] !== 'all') {
            $query->where('service_type', 'otp')->where('otp_category', (string) $filters['otp_category']);
        }

        // 3. Direction (outbound = sent, inbound = received)
        if (!empty($filters['direction']) && $filters['direction'] !== 'all') {
            $query->where('direction', $filters['direction']);
        }

        // 4. Single / Bulk
        if (isset($filters['is_bulk']) && $filters['is_bulk'] !== '' && $filters['is_bulk'] !== 'all') {
            $isBulk = in_array($filters['is_bulk'], [1, '1', true, 'true', 'bulk'], true);
            $query->where('is_bulk', $isBulk);
        }

        // 5. Status
        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            $query->where('status', $filters['status']);
        }

        // 6. Sender Number
        if (!empty($filters['sender_number_id']) && $filters['sender_number_id'] !== 'all') {
            $query->where('sender_number_id', (int) $filters['sender_number_id']);
        }
        if (!empty($filters['sender_phone'])) {
            $query->where('sender_phone', 'like', '%' . trim($filters['sender_phone']) . '%');
        }

        // 7. Recipient Phone
        if (!empty($filters['recipient_phone'])) {
            $query->where('recipient_phone', 'like', '%' . trim($filters['recipient_phone']) . '%');
        }

        // 8. Message Group
        if (!empty($filters['sms_message_group_id']) && $filters['sms_message_group_id'] !== 'all') {
            $query->where('sms_message_group_id', (int) $filters['sms_message_group_id']);
        } elseif (!empty($filters['group']) && $filters['group'] !== 'all') {
            if (is_numeric($filters['group'])) {
                $query->where('sms_message_group_id', (int) $filters['group']);
            } else {
                $query->whereHas('group', fn ($gq) => $gq->where('name', 'like', '%' . $filters['group'] . '%'));
            }
        }

        // 9. Request ID
        if (!empty($filters['request_id'])) {
            $query->where('request_id', (int) $filters['request_id']);
        }

        // 10. User ID
        if (!empty($filters['user_id'])) {
            $query->where('user_id', (int) $filters['user_id']);
        }

        // 11. Date range
        $dateFrom = $this->parseDate($filters['date_from'] ?? $filters['from_date'] ?? null);
        if ($dateFrom) {
            $query->where('created_at', '>=', $dateFrom->startOfDay());
        }

        $dateTo = $this->parseDate($filters['date_to'] ?? $filters['to_date'] ?? null);
        if ($dateTo) {
            $query->where('created_at', '<=', $dateTo->endOfDay());
        }

        // 12. Search keyword (across recipient, sender, tracking ID, content)
        if (!empty($filters['search'])) {
            $search = trim($filters['search']);
            $query->where(function (Builder $sq) use ($search) {
                $sq->where('recipient_phone', 'like', "%{$search}%")
                    ->orWhere('sender_phone', 'like', "%{$search}%")
                    ->orWhere('tracking_id', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%");
            });
        }

        return $query->orderByDesc('id');
    }

    /**
     * Get paginated logs.
     *
     * @param array<string, mixed> $filters
     * @param GhorfeOnlineList|null $business
     * @param bool $isSuperAdmin
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getPaginatedLogs(
        array $filters = [],
        ?GhorfeOnlineList $business = null,
        bool $isSuperAdmin = false,
        int $perPage = 20,
        bool $canUseOtp = true
    ): LengthAwarePaginator {
        $logs = $this->buildQuery($filters, $business, $isSuperAdmin, $canUseOtp)->paginate($perPage);

        app(CatalogBreadcrumbResolver::class)->annotateMessages($logs->getCollection());

        return $logs;
    }

    /**
     * Calculate summary metrics based on current filters.
     *
     * @param array<string, mixed> $filters
     * @param GhorfeOnlineList|null $business
     * @param bool $isSuperAdmin
     * @return array<string, mixed>
     */
    public function getSummaryMetrics(
        array $filters = [],
        ?GhorfeOnlineList $business = null,
        bool $isSuperAdmin = false,
        bool $canUseOtp = true
    ): array {
        $baseQuery = $this->buildQuery($filters, $business, $isSuperAdmin, $canUseOtp);

        $total = (clone $baseQuery)->count();
        $outbound = (clone $baseQuery)->where('direction', 'outbound')->count();
        $inbound = (clone $baseQuery)->where('direction', 'inbound')->count();
        $delivered = (clone $baseQuery)->where(function ($q) {
            $q->where('status', 'delivered')->orWhere('delivery_status', 'delivered');
        })->count();
        $failed = (clone $baseQuery)->where(function ($q) {
            $q->where('status', 'failed')->orWhere('delivery_status', 'failed');
        })->count();
        $totalCost = (clone $baseQuery)->sum('cost');

        return [
            'total' => $total,
            'outbound' => $outbound,
            'inbound' => $inbound,
            'delivered' => $delivered,
            'failed' => $failed,
            'total_cost' => (float) $totalCost,
        ];
    }

    /**
     * Parse date string safely from Gregorian or Jalali.
     */
    protected function parseDate(?string $dateStr): ?Carbon
    {
        if (empty($dateStr)) {
            return null;
        }

        $dateStr = trim($dateStr);

        try {
            // Check if Jalali format (year starting with 13 or 14, e.g. 1403/05/12 or 1403-05-12)
            if (preg_match('/^(13|14)\d{2}[\/\-]\d{1,2}[\/\-]\d{1,2}$/', $dateStr)) {
                $v = Verta::parse($dateStr);
                return $v->DateTime ? Carbon::instance($v->DateTime) : null;
            }

            return Carbon::parse($dateStr);
        } catch (\Throwable) {
            return null;
        }
    }
}
