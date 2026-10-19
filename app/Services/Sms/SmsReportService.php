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
     * Direction filter («جهت پیام»): only 'inbound' / 'outbound' are honoured, anything else means "all".
     */
    protected function applyDirectionFilter(Builder $query, array $filters): Builder
    {
        $direction = $filters['direction'] ?? null;
        if (in_array($direction, ['inbound', 'outbound'], true)) {
            $query->where('direction', $direction);
        }

        return $query;
    }

    /**
     * Routing of the advertising report («تبلیغاتی و عادی»): every non-OTP row, outbound or inbound
     * (service_type advertising / normal / unset), so each message lands on exactly one of the
     * OTP / advertising reports. Only service_type = otp belongs to the OTP report.
     */
    protected function applyAdvertisingRouting(Builder $query): Builder
    {
        return $query->where(fn ($t) => $t->whereNull('service_type')->orWhere('service_type', '!=', 'otp'));
    }

    /**
     * Service-type filter («نوع پیام») of the advertising report: 'advertising' or 'normal'
     * (normal also covers rows without a service_type); anything else means "all".
     */
    protected function applyAdvertisingServiceTypeFilter(Builder $query, array $filters): Builder
    {
        $type = $filters['service_type'] ?? null;
        if ($type === 'advertising') {
            $query->where('service_type', 'advertising');
        } elseif ($type === 'normal') {
            $query->where(fn ($t) => $t->whereNull('service_type')->orWhere('service_type', 'normal'));
        }

        return $query;
    }

    /**
     * Sent-vs-received tiles. Cost, delivery and failure stats are computed on OUTBOUND rows only
     * (received messages cost 0 and are always "delivered", which would distort the rates).
     *
     * @return array<string, int|float>
     */
    protected function directionStats(Builder $query): array
    {
        $out = (clone $query)->where('direction', 'outbound');
        $outbound = (clone $out)->count();
        $delivered = (clone $out)->whereIn('status', ['delivered', 'sent'])->count();

        return [
            'total_messages' => $outbound,
            'outbound_count' => $outbound,
            'inbound_count' => (clone $query)->where('direction', 'inbound')->count(),
            'all_count' => (clone $query)->count(),
            'total_cost' => (float) (clone $out)->sum('cost'),
            'delivered_count' => $delivered,
            'failed_count' => (clone $out)->where('status', 'failed')->count(),
            'queued_count' => (clone $out)->where('status', 'queued')->count(),
            'delivery_rate' => $outbound > 0 ? round(($delivered / $outbound) * 100, 1) : 0,
        ];
    }

    /**
     * Build base query for OTP report of a specific business.
     */
    public function buildOtpReportQuery(GhorfeOnlineList $business, array $filters = []): Builder
    {
        $query = SmsMessage::query()
            ->with(['template', 'group', 'senderNumber', 'user', 'creator'])
            ->where('ghorfe_online_list_id', $business->id)
            ->where('service_type', 'otp');

        $this->applyDirectionFilter($query, $filters);

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

        $summary = $this->directionStats($baseQuery);

        $messages = $baseQuery->paginate($perPage)->withQueryString();
        app(CatalogBreadcrumbResolver::class)->annotateMessages($messages->getCollection());

        // Available filter options. `type` (confidential/normal, added in
        // 509f35d7) is a different, auto-derived column — service_type is
        // what distinguishes an OTP template (see scopeForOtp()).
        $templates = SmsMessageTemplate::query()->where('service_type', 'otp')->get();
        $groups = SmsMessageGroup::query()->get();

        return [
            'business' => $business,
            'filters' => $filters,
            'summary' => $summary,
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
            ->where('ghorfe_online_list_id', $business->id);

        $this->applyAdvertisingRouting($query);
        $this->applyAdvertisingServiceTypeFilter($query, $filters);
        $this->applyDirectionFilter($query, $filters);

        if (!empty($filters['sender_number_id'])) {
            // Inbound rows have no sender_number_id: they are tied to the line by the number they were received on.
            $senderId = (int) $filters['sender_number_id'];
            $senderNumber = SenderNumber::query()->whereKey($senderId)->value('number');
            $query->where(function ($w) use ($senderId, $senderNumber) {
                $w->where('sender_number_id', $senderId);
                if ($senderNumber) {
                    $w->orWhere(fn ($i) => $i->where('direction', 'inbound')->where('recipient_phone', $senderNumber));
                }
            });
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

        // Summary metrics (cost / delivery on outbound rows only, received counted separately)
        $summary = $this->directionStats($messagesQuery);
        $summary['total_campaigns'] = (clone $campaignsQuery)->count();
        $summary['total_recipients'] = (int) (clone $campaignsQuery)->sum('total_recipients');

        // Per-sender breakdown
        $senderBreakdown = $assignedSenders->map(function ($sender) use ($business, $filters) {
            $senderFilter = array_merge($filters, ['sender_number_id' => $sender->id]);
            $msgQ = $this->buildAdvertisingMessagesQuery($business, $senderFilter);
            $campQ = $this->buildAdvertisingCampaignsQuery($business, $senderFilter);

            $out = (clone $msgQ)->where('direction', 'outbound');
            $msgCount = (clone $out)->count();
            $inboundCount = (clone $msgQ)->where('direction', 'inbound')->count();
            $cost = (float) (clone $out)->sum('cost');
            $success = (clone $out)->whereIn('status', ['delivered', 'sent'])->count();
            $campsCount = (clone $campQ)->count();

            return [
                'sender' => $sender,
                'messages_count' => $msgCount,
                'inbound_count' => $inboundCount,
                'campaigns_count' => $campsCount,
                'cost' => $cost,
                'success_count' => $success,
                'success_rate' => $msgCount > 0 ? round(($success / $msgCount) * 100, 1) : 0,
            ];
        });

        $messages = $messagesQuery->paginate($perPage, ['*'], 'messages_page')->withQueryString();
        app(CatalogBreadcrumbResolver::class)->annotateMessages($messages->getCollection());
        $campaigns = $campaignsQuery->paginate(15, ['*'], 'campaigns_page')->withQueryString();
        $groups = SmsMessageGroup::query()->get();

        return [
            'business' => $business,
            'filters' => $filters,
            'assigned_senders' => $assignedSenders,
            'summary' => $summary,
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

        $this->applyDirectionFilter($query, $filters);

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

        // Overall KPIs (cost / delivery on outbound rows only, received counted separately)
        $summary = $this->directionStats($baseQuery);

        // Unique businesses count in this filtered dataset
        $businessesCount = (clone $baseQuery)->distinct('ghorfe_online_list_id')->count('ghorfe_online_list_id');
        $summary['businesses_count'] = $businessesCount;

        // Breakdowns below are outbound-only (volume and cost of what was actually sent)
        $outboundQuery = (clone $baseQuery)->where('direction', 'outbound');

        // Breakdown by message type (service_type)
        $messageTypeBreakdown = (clone $outboundQuery)
            ->selectRaw('service_type, COUNT(*) as count, SUM(cost) as cost')
            ->groupBy('service_type')
            ->get()
            ->keyBy('service_type');

        // Top businesses by SMS consumption
        $businessBreakdown = (clone $outboundQuery)
            ->whereNotNull('ghorfe_online_list_id')
            ->selectRaw('ghorfe_online_list_id, COUNT(*) as message_count, SUM(cost) as total_cost')
            ->groupBy('ghorfe_online_list_id')
            ->orderByDesc('message_count')
            ->limit(10)
            ->with('business')
            ->get();

        // Breakdown by sender number
        $senderBreakdown = (clone $outboundQuery)
            ->whereNotNull('sender_number_id')
            ->selectRaw('sender_number_id, COUNT(*) as message_count, SUM(cost) as total_cost')
            ->groupBy('sender_number_id')
            ->orderByDesc('message_count')
            ->limit(10)
            ->with('senderNumber')
            ->get();

        $messages = $baseQuery->paginate($perPage)->withQueryString();
        app(CatalogBreadcrumbResolver::class)->annotateMessages($messages->getCollection());

        // Filter selector options
        $businesses = GhorfeOnlineList::query()->select('id', 'title')->orderBy('title')->get();
        $senders = SenderNumber::query()->select('id', 'number', 'type')->orderBy('number')->get();
        $groups = SmsMessageGroup::query()->select('id', 'name')->orderBy('name')->get();
        $panelTypes = ['otp' => 'اعتبارسنجی (OTP)', 'advertising' => 'تبلیغاتی (Advertising)', 'normal-advertising' => 'عادی/تبلیغاتی', 'all' => 'همه پنل‌ها'];

        return [
            'filters' => $filters,
            'summary' => $summary,
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
