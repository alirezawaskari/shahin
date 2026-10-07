<?php

namespace App\Services\Sms;

use App\Models\GhorfeOnlineList;
use App\Models\SenderNumber;
use App\Models\SmsCampaign;
use App\Models\SmsMessage;
use App\Models\SmsMessageGroup;
use App\Models\SmsMessageTemplate;
use App\Models\SmsPanel;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SmsReportService
{
    /**
     * Build base query for OTP report of a specific business.
     */
    public function buildOtpReportQuery(GhorfeOnlineList $business, array $filters = []): Builder
    {
        $query = SmsMessage::query()
            ->with(['template', 'group', 'senderNumber', 'user', 'creator'])
            ->where('ghorfe_online_list_id', $business->id)
            ->where('service_type', 'otp');

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['sms_message_template_id'])) {
            $query->where('sms_message_template_id', $filters['sms_message_template_id']);
        }

        if (!empty($filters['sms_message_group_id'])) {
            $query->where('sms_message_group_id', $filters['sms_message_group_id']);
        }

        if (!empty($filters['search'])) {
            $search = trim($filters['search']);
            $query->where(function ($q) use ($search) {
                $q->where('recipient_phone', 'like', "%{$search}%")
                  ->orWhere('tracking_id', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['date_from'])) {
            $query->where('created_at', '>=', Carbon::parse($filters['date_from'])->startOfDay());
        }

        if (!empty($filters['date_to'])) {
            $query->where('created_at', '<=', Carbon::parse($filters['date_to'])->endOfDay());
        }

        return $query->orderByDesc('id');
    }

    /**
     * Get OTP report summary and paginated data for a business.
     *
     * @return array<string, mixed>
     */
    public function getOtpReport(GhorfeOnlineList $business, array $filters = [], int $perPage = 25): array
    {
        $baseQuery = $this->buildOtpReportQuery($business, $filters);

        // Calculate KPI summary
        $summaryQuery = clone $baseQuery;
        $totalMessages = $summaryQuery->count();
        $totalCost = (float) (clone $baseQuery)->sum('cost');
        $deliveredCount = (clone $baseQuery)->whereIn('status', ['delivered', 'sent'])->count();
        $failedCount = (clone $baseQuery)->where('status', 'failed')->count();
        $queuedCount = (clone $baseQuery)->where('status', 'queued')->count();

        $deliveryRate = $totalMessages > 0 ? round(($deliveredCount / $totalMessages) * 100, 1) : 0;

        $messages = $baseQuery->paginate($perPage);

        // Available filter options
        $templates = SmsMessageTemplate::query()->where('type', 'otp')->get();
        $groups = SmsMessageGroup::query()->get();

        return [
            'business' => $business,
            'filters' => $filters,
            'summary' => [
                'total_messages' => $totalMessages,
                'total_cost' => $totalCost,
                'delivered_count' => $deliveredCount,
                'failed_count' => $failedCount,
                'queued_count' => $queuedCount,
                'delivery_rate' => $deliveryRate,
            ],
            'messages' => $messages,
            'templates' => $templates,
            'groups' => $groups,
        ];
    }

    /**
     * Build base query for Advertising messages of a business.
     */
    public function buildAdvertisingMessagesQuery(GhorfeOnlineList $business, array $filters = []): Builder
    {
        $query = SmsMessage::query()
            ->with(['template', 'group', 'senderNumber', 'campaign', 'creator'])
            ->where('ghorfe_online_list_id', $business->id)
            ->where('service_type', 'advertising');

        if (!empty($filters['sender_number_id'])) {
            $query->where('sender_number_id', $filters['sender_number_id']);
        }

        if (!empty($filters['sms_campaign_id'])) {
            $query->where('sms_campaign_id', $filters['sms_campaign_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['sms_message_group_id'])) {
            $query->where('sms_message_group_id', $filters['sms_message_group_id']);
        }

        if (!empty($filters['search'])) {
            $search = trim($filters['search']);
            $query->where(function ($q) use ($search) {
                $q->where('recipient_phone', 'like', "%{$search}%")
                  ->orWhere('tracking_id', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['date_from'])) {
            $query->where('created_at', '>=', Carbon::parse($filters['date_from'])->startOfDay());
        }

        if (!empty($filters['date_to'])) {
            $query->where('created_at', '<=', Carbon::parse($filters['date_to'])->endOfDay());
        }

        return $query->orderByDesc('id');
    }

    /**
     * Build base query for Advertising campaigns of a business.
     */
    public function buildAdvertisingCampaignsQuery(GhorfeOnlineList $business, array $filters = []): Builder
    {
        $query = SmsCampaign::query()
            ->with(['senderNumber', 'template', 'creator'])
            ->where('ghorfe_online_list_id', $business->id);

        if (!empty($filters['sender_number_id'])) {
            $query->where('sender_number_id', $filters['sender_number_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['date_from'])) {
            $query->where('created_at', '>=', Carbon::parse($filters['date_from'])->startOfDay());
        }

        if (!empty($filters['date_to'])) {
            $query->where('created_at', '<=', Carbon::parse($filters['date_to'])->endOfDay());
        }

        return $query->orderByDesc('id');
    }

    /**
     * Get Advertising report (per-sender, combining campaigns and messages).
     *
     * @return array<string, mixed>
     */
    public function getAdvertisingReport(GhorfeOnlineList $business, array $filters = [], int $perPage = 25): array
    {
        $assignedSenders = $business->senderNumbers()
            ->where('sender_numbers.type', 'advertising')
            ->where('sender_numbers.status', 'active')
            ->get();

        $messagesQuery = $this->buildAdvertisingMessagesQuery($business, $filters);
        $campaignsQuery = $this->buildAdvertisingCampaignsQuery($business, $filters);

        // Summary metrics
        $totalMessages = (clone $messagesQuery)->count();
        $totalCost = (float) (clone $messagesQuery)->sum('cost');
        $deliveredCount = (clone $messagesQuery)->whereIn('status', ['delivered', 'sent'])->count();
        $failedCount = (clone $messagesQuery)->where('status', 'failed')->count();

        $totalCampaigns = (clone $campaignsQuery)->count();
        $totalRecipients = (int) (clone $campaignsQuery)->sum('total_recipients');

        $deliveryRate = $totalMessages > 0 ? round(($deliveredCount / $totalMessages) * 100, 1) : 0;

        // Per-sender breakdown
        $senderBreakdown = $assignedSenders->map(function ($sender) use ($business, $filters) {
            $senderFilter = array_merge($filters, ['sender_number_id' => $sender->id]);
            $msgQ = $this->buildAdvertisingMessagesQuery($business, $senderFilter);
            $campQ = $this->buildAdvertisingCampaignsQuery($business, $senderFilter);

            $msgCount = (clone $msgQ)->count();
            $cost = (float) (clone $msgQ)->sum('cost');
            $success = (clone $msgQ)->whereIn('status', ['delivered', 'sent'])->count();
            $campsCount = (clone $campQ)->count();

            return [
                'sender' => $sender,
                'messages_count' => $msgCount,
                'campaigns_count' => $campsCount,
                'cost' => $cost,
                'success_count' => $success,
                'success_rate' => $msgCount > 0 ? round(($success / $msgCount) * 100, 1) : 0,
            ];
        });

        $messages = $messagesQuery->paginate($perPage, ['*'], 'messages_page');
        $campaigns = $campaignsQuery->paginate(15, ['*'], 'campaigns_page');
        $groups = SmsMessageGroup::query()->get();

        return [
            'business' => $business,
            'filters' => $filters,
            'assigned_senders' => $assignedSenders,
            'summary' => [
                'total_messages' => $totalMessages,
                'total_cost' => $totalCost,
                'delivered_count' => $deliveredCount,
                'failed_count' => $failedCount,
                'total_campaigns' => $totalCampaigns,
                'total_recipients' => $totalRecipients,
                'delivery_rate' => $deliveryRate,
            ],
            'sender_breakdown' => $senderBreakdown,
            'messages' => $messages,
            'campaigns' => $campaigns,
            'groups' => $groups,
        ];
    }

    /**
     * Build base query for Super Admin cross-business report.
     * Supported filters: business, sender, panel type, message type, date, status, group.
     */
    public function buildSuperAdminReportQuery(array $filters = []): Builder
    {
        $query = SmsMessage::query()
            ->with(['business', 'senderNumber', 'template', 'group', 'campaign', 'creator']);

        // 1. Business filter
        if (!empty($filters['business'])) {
            $businessVal = $filters['business'];
            if (is_numeric($businessVal)) {
                $query->where('ghorfe_online_list_id', (int) $businessVal);
            } else {
                $query->whereHas('business', function ($q) use ($businessVal) {
                    $q->where('title', 'like', "%{$businessVal}%")
                      ->orWhere('slug', $businessVal);
                });
            }
        } elseif (!empty($filters['ghorfe_online_list_id'])) {
            $query->where('ghorfe_online_list_id', (int) $filters['ghorfe_online_list_id']);
        }

        // 2. Sender filter
        if (!empty($filters['sender'])) {
            $senderVal = $filters['sender'];
            if (is_numeric($senderVal)) {
                $query->where('sender_number_id', (int) $senderVal);
            } else {
                $query->where(function ($q) use ($senderVal) {
                    $q->where('sender_phone', 'like', "%{$senderVal}%")
                      ->orWhereHas('senderNumber', fn ($sq) => $sq->where('number', 'like', "%{$senderVal}%"));
                });
            }
        } elseif (!empty($filters['sender_number_id'])) {
            $query->where('sender_number_id', (int) $filters['sender_number_id']);
        }

        // 3. Panel type filter
        if (!empty($filters['panel_type'])) {
            $panelType = $filters['panel_type'];
            $query->whereHas('senderNumber', function ($sq) use ($panelType) {
                $sq->where(function ($inner) use ($panelType) {
                    $inner->where('type', $panelType)
                        ->orWhereHas('panel', fn ($pq) => $pq->where('type', $panelType));
                });
            });
        }

        // 4. Message type filter (service_type: otp, normal, advertising)
        if (!empty($filters['message_type'])) {
            $query->where('service_type', $filters['message_type']);
        } elseif (!empty($filters['service_type'])) {
            $query->where('service_type', $filters['service_type']);
        }

        // 5. Date filter
        if (!empty($filters['date_from'])) {
            $query->where('created_at', '>=', Carbon::parse($filters['date_from'])->startOfDay());
        }

        if (!empty($filters['date_to'])) {
            $query->where('created_at', '<=', Carbon::parse($filters['date_to'])->endOfDay());
        }

        // 6. Status filter
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // 7. Group filter
        if (!empty($filters['group'])) {
            $groupVal = $filters['group'];
            if (is_numeric($groupVal)) {
                $query->where('sms_message_group_id', (int) $groupVal);
            } else {
                $query->whereHas('group', fn ($gq) => $gq->where('name', 'like', "%{$groupVal}%"));
            }
        } elseif (!empty($filters['sms_message_group_id'])) {
            $query->where('sms_message_group_id', (int) $filters['sms_message_group_id']);
        }

        // Additional general search
        if (!empty($filters['search'])) {
            $search = trim($filters['search']);
            $query->where(function ($q) use ($search) {
                $q->where('recipient_phone', 'like', "%{$search}%")
                  ->orWhere('sender_phone', 'like', "%{$search}%")
                  ->orWhere('tracking_id', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        return $query->orderByDesc('id');
    }

    /**
     * Get cross-business report for Super Admin with KPIs and breakdowns.
     *
     * @return array<string, mixed>
     */
    public function getSuperAdminReport(array $filters = [], int $perPage = 30): array
    {
        $baseQuery = $this->buildSuperAdminReportQuery($filters);

        // Overall KPIs
        $totalMessages = (clone $baseQuery)->count();
        $totalCost = (float) (clone $baseQuery)->sum('cost');
        $deliveredCount = (clone $baseQuery)->whereIn('status', ['delivered', 'sent'])->count();
        $failedCount = (clone $baseQuery)->where('status', 'failed')->count();
        $deliveryRate = $totalMessages > 0 ? round(($deliveredCount / $totalMessages) * 100, 1) : 0;

        // Unique businesses count in this filtered dataset
        $businessesCount = (clone $baseQuery)->distinct('ghorfe_online_list_id')->count('ghorfe_online_list_id');

        // Breakdown by message type (service_type)
        $messageTypeBreakdown = (clone $baseQuery)
            ->selectRaw('service_type, COUNT(*) as count, SUM(cost) as cost')
            ->groupBy('service_type')
            ->get()
            ->keyBy('service_type');

        // Top businesses by SMS consumption
        $businessBreakdown = (clone $baseQuery)
            ->whereNotNull('ghorfe_online_list_id')
            ->selectRaw('ghorfe_online_list_id, COUNT(*) as message_count, SUM(cost) as total_cost')
            ->groupBy('ghorfe_online_list_id')
            ->orderByDesc('message_count')
            ->limit(10)
            ->with('business')
            ->get();

        // Breakdown by sender number
        $senderBreakdown = (clone $baseQuery)
            ->whereNotNull('sender_number_id')
            ->selectRaw('sender_number_id, COUNT(*) as message_count, SUM(cost) as total_cost')
            ->groupBy('sender_number_id')
            ->orderByDesc('message_count')
            ->limit(10)
            ->with('senderNumber')
            ->get();

        $messages = $baseQuery->paginate($perPage);

        // Filter selector options
        $businesses = GhorfeOnlineList::query()->select('id', 'title')->orderBy('title')->get();
        $senders = SenderNumber::query()->select('id', 'number', 'type')->orderBy('number')->get();
        $groups = SmsMessageGroup::query()->select('id', 'name')->orderBy('name')->get();
        $panelTypes = ['otp' => 'اعتبارسنجی (OTP)', 'advertising' => 'تبلیغاتی (Advertising)', 'normal-advertising' => 'عادی/تبلیغاتی', 'all' => 'همه پنل‌ها'];

        return [
            'filters' => $filters,
            'summary' => [
                'total_messages' => $totalMessages,
                'total_cost' => $totalCost,
                'businesses_count' => $businessesCount,
                'delivered_count' => $deliveredCount,
                'failed_count' => $failedCount,
                'delivery_rate' => $deliveryRate,
            ],
            'message_type_breakdown' => $messageTypeBreakdown,
            'business_breakdown' => $businessBreakdown,
            'sender_breakdown' => $senderBreakdown,
            'messages' => $messages,
            'businesses' => $businesses,
            'senders' => $senders,
            'groups' => $groups,
            'panel_types' => $panelTypes,
        ];
    }
}
