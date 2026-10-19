<?php

namespace App\Services\SmsSettings;

use App\Models\SmsMessageGroup;
use App\Models\SmsMessageTemplate;
use App\Models\SmsMessageTemplateHistory;
use App\Models\User;
use App\Services\Sms\DynamicParameterResolver;
use App\Services\Sms\SmsSenderInfoResolver;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class SmsMessageTemplateService
{
    public function __construct(
        protected DynamicParameterResolver $parameterResolver,
        protected ?SmsSenderInfoResolver $senderInfoResolver = null
    ) {
        $this->senderInfoResolver = $senderInfoResolver ?? app(SmsSenderInfoResolver::class);
    }

    /**
     * List templates with filters.
     */
    public function list(array $filters = [], ?int $businessId = null, bool $includeOtp = true): LengthAwarePaginator
    {
        $query = SmsMessageTemplate::with(['group.parent', 'creator', 'updater', 'business:id,title'])
            ->withCount('histories')
            ->latest('id');

        if ($businessId) {
            $query->where('ghorfe_online_list_id', $businessId);
        }

        if (!$includeOtp) {
            $query->where(fn ($q) => $q->whereNull('service_type')->orWhere('service_type', '!=', 'otp'));
        }

        if (!empty($filters['service_type']) && $filters['service_type'] !== 'all') {
            $query->where('service_type', $filters['service_type']);
        }

        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['group_id']) && $filters['group_id'] !== 'all') {
            $query->where('sms_message_group_id', $filters['group_id']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%");
            });
        }

        return $query->paginate($filters['per_page'] ?? 15);
    }

    /**
     * Create a template.
     */
    public function create(array $data, ?int $userId = null, ?int $businessId = null): SmsMessageTemplate
    {
        $userId = $userId ?: Auth::id();

        $template = SmsMessageTemplate::create([
            'title' => $data['title'],
            'content' => $data['content'],
            'sms_message_group_id' => $data['sms_message_group_id'] ?? null,
            'ghorfe_online_list_id' => $businessId,
            'service_type' => $data['service_type'] ?? 'normal',
            'otp_category' => ($data['service_type'] ?? 'normal') === 'otp' ? ($data['otp_category'] ?? null) : null,
            'status' => $data['status'] ?? 'active',
            'created_by' => $userId,
            'updated_by' => $userId,
        ]);

        // Observer automatically creates initial history if Auth::id() is set.
        // Fallback for non-auth contexts:
        if (!Auth::id() && $userId) {
            SmsMessageTemplateHistory::firstOrCreate(
                [
                    'sms_message_template_id' => $template->id,
                    'content' => $template->content,
                ],
                [
                    'changed_by' => $userId,
                ]
            );
        }

        return $template;
    }

    /**
     * Update an existing template.
     * Guaranteed to trigger Eloquent model save so the SmsMessageTemplateObserver fires.
     */
    public function update(SmsMessageTemplate $template, array $data, ?int $userId = null): SmsMessageTemplate
    {
        $userId = $userId ?: Auth::id() ?: $template->created_by;

        $template->title = $data['title'] ?? $template->title;
        $template->content = $data['content'] ?? $template->content;
        $template->sms_message_group_id = array_key_exists('sms_message_group_id', $data)
            ? $data['sms_message_group_id']
            : $template->sms_message_group_id;
        $template->service_type = $data['service_type'] ?? $template->service_type;
        // The OTP category only exists on OTP templates; switching away from OTP clears it.
        $template->otp_category = $template->service_type === 'otp'
            ? ($data['otp_category'] ?? $template->otp_category)
            : null;
        $template->status = $data['status'] ?? $template->status;
        $template->updated_by = $userId;

        // Model save fires observer if content was changed!
        $template->save();

        return $template;
    }

    /**
     * Delete a template.
     */
    public function delete(SmsMessageTemplate $template): bool
    {
        return (bool) $template->delete();
    }

    /**
     * Toggle active/inactive status.
     */
    public function toggleStatus(SmsMessageTemplate $template): SmsMessageTemplate
    {
        $template->status = ($template->status === 'active') ? 'inactive' : 'active';
        $template->updated_by = Auth::id() ?: $template->updated_by;
        $template->save();

        return $template;
    }

    /**
     * Generate a live preview of the template content using DynamicParameterResolver.
     */
    public function preview(string $content, array $customContext = []): array
    {
        // 1. Resolve standard DynamicParameterResolver tokens
        $defaultContext = [
            'first_name' => 'علی',
            'name' => 'علی',
            'family' => 'رضایی',
            'last_name' => 'رضایی',
            'birth_date' => '1372/04/15',
            'birth_day' => '15',
            'birth_month' => '04',
            'recipient_phone' => '09123456789',
            'phone' => '09123456789',
            'company_name' => ghorfe()?->title ?? 'فروشگاه لاله',
            'business_name' => ghorfe()?->title ?? 'فروشگاه لاله',
            'date' => verta()->format('Y/m/d'),
            'time' => verta()->format('H:i'),
            'factor_id' => '10482',
            'payment_link' => 'lalecard.ir/pf/10482',
            'request_id' => 'REQ-8821',
        ];

        $mergedContext = array_merge($defaultContext, $customContext);
        $resolved = $this->parameterResolver->applyTo($content, $mergedContext);

        // 2. Resolve sender info placeholders {company_name}, {phone}, {address}
        $resolved = $this->senderInfoResolver->applyTo($resolved);

        // Find which placeholders are used in the template
        $placeholders = [];
        preg_match_all('/\{[^\}]+\}/u', $content, $matches);
        if (!empty($matches[0])) {
            $placeholders = array_values(array_unique($matches[0]));
        }

        return [
            'raw_content' => $content,
            'preview' => $resolved,
            'placeholders' => $placeholders,
            'char_count' => mb_strlen($resolved, 'UTF-8'),
        ];
    }

    /**
     * Retrieve history of revisions and structured diff for a template.
     */
    public function getHistoryWithDiff(SmsMessageTemplate $template, ?int $historyId = null): array
    {
        $histories = $template->histories()
            ->with('changer')
            ->orderBy('id', 'desc')
            ->get();

        $selectedHistory = null;
        $previousHistory = null;

        if ($historyId) {
            $selectedHistory = $histories->firstWhere('id', $historyId);
        } else {
            $selectedHistory = $histories->first();
        }

        if ($selectedHistory) {
            // Find previous version right before this one
            $previousHistory = $histories
                ->where('id', '<', $selectedHistory->id)
                ->sortByDesc('id')
                ->first();
        }

        $oldText = $previousHistory ? $previousHistory->content : '';
        $newText = $selectedHistory ? $selectedHistory->content : $template->content;

        $diff = $this->computeDiff($oldText, $newText);

        return [
            'template' => $template,
            'histories' => $histories,
            'selected_history' => $selectedHistory,
            'previous_history' => $previousHistory,
            'diff' => $diff,
        ];
    }

    /**
     * Compute a simple line-based text diff.
     */
    public function computeDiff(string $oldText, string $newText): array
    {
        $oldLines = explode("\n", str_replace("\r", "", $oldText));
        $newLines = explode("\n", str_replace("\r", "", $newText));

        $diff = [];
        $max = max(count($oldLines), count($newLines));

        for ($i = 0; $i < $max; $i++) {
            $old = $oldLines[$i] ?? null;
            $new = $newLines[$i] ?? null;

            if ($old === $new) {
                $diff[] = [
                    'type' => 'same',
                    'line' => $new,
                ];
            } else {
                if ($old !== null && $new !== null) {
                    $diff[] = [
                        'type' => 'changed',
                        'old' => $old,
                        'new' => $new,
                    ];
                } elseif ($old !== null) {
                    $diff[] = [
                        'type' => 'removed',
                        'line' => $old,
                    ];
                } elseif ($new !== null) {
                    $diff[] = [
                        'type' => 'added',
                        'line' => $new,
                    ];
                }
            }
        }

        return $diff;
    }
}
