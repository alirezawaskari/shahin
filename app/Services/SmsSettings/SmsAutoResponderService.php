<?php

namespace App\Services\SmsSettings;

use App\Models\SmsAutoResponder;
use App\Services\Sms\SmsKeywordNormalizer;
use App\Services\Sms\SmsReservedCodes;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

/**
 * Rule CRUD for the per-business SMS auto-responder plus the collision validator required by the brief:
 * a trigger may neither map to a reserved system code nor duplicate / overlap another ACTIVE trigger
 * of the same business. Inactive and soft-deleted rules never block.
 */
class SmsAutoResponderService
{
    public function list(?int $businessId, array $filters = []): LengthAwarePaginator
    {
        $query = SmsAutoResponder::with(['business', 'creator', 'senderNumber'])->latest('id');

        if ($businessId) {
            $query->where('ghorfe_online_list_id', $businessId);
        }

        if (($filters['status'] ?? 'all') === 'active') {
            $query->where('is_active', true);
        } elseif (($filters['status'] ?? 'all') === 'inactive') {
            $query->where('is_active', false);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('trigger_keyword', 'like', "%{$search}%")
                    ->orWhere('reply_text', 'like', "%{$search}%");
            });
        }

        return $query->paginate($filters['per_page'] ?? 15);
    }

    /**
     * Collision check. Returns Persian messages (empty array = trigger is allowed).
     *
     * @param int|null $ignoreRuleId the rule being edited (a rule never conflicts with itself)
     * @return string[]
     */
    public function validateTrigger(int $businessId, string $keyword, string $matchType, ?int $ignoreRuleId = null): array
    {
        $normalized = SmsKeywordNormalizer::normalize($keyword);
        if ($normalized === '') {
            return ['کلمه کلیدی نمی‌تواند خالی باشد.'];
        }

        $errors = [];
        $reserved = SmsReservedCodes::all();

        if ($hit = SmsReservedCodes::find($keyword, $reserved)) {
            $errors[] = sprintf(
                'کلمه «%s» یک کد رزروشده سیستم است (%s) و نمی‌تواند به‌عنوان محرک پاسخ‌گوی خودکار استفاده شود.',
                $keyword,
                $hit['source']
            );
        } elseif ($matchType === SmsAutoResponder::MATCH_STARTS_WITH && ($hit = SmsReservedCodes::shadowedBy($normalized, $reserved))) {
            $errors[] = sprintf(
                'پیشوند «%s» کدهای رزروشده سیستم مانند «%s» (%s) را نیز پوشش می‌دهد؛ پیشوند دقیق‌تری انتخاب کنید.',
                $keyword,
                $hit['code'],
                $hit['source']
            );
        }

        $others = SmsAutoResponder::query()
            ->where('ghorfe_online_list_id', $businessId)
            ->where('is_active', true)
            ->when($ignoreRuleId, fn ($q) => $q->where('id', '!=', $ignoreRuleId))
            ->get();

        foreach ($others as $other) {
            $otherKey = (string) $other->normalized_keyword;
            if ($this->overlaps($normalized, $matchType, $otherKey, $other->match_type)) {
                $errors[] = sprintf(
                    'این کلمه با قانون فعال «%s» (محرک «%s»، %s) تداخل دارد. ابتدا آن قانون را ویرایش یا غیرفعال کنید.',
                    $other->name,
                    $other->trigger_keyword,
                    SmsAutoResponder::matchTypeLabel($other->match_type)
                );
            }
        }

        return $errors;
    }

    /**
     * Do two triggers (normalized) ever match the same inbound text?
     */
    public function overlaps(string $a, string $typeA, string $b, string $typeB): bool
    {
        $aPrefix = $typeA === SmsAutoResponder::MATCH_STARTS_WITH;
        $bPrefix = $typeB === SmsAutoResponder::MATCH_STARTS_WITH;

        if (!$aPrefix && !$bPrefix) {
            return $a === $b;
        }
        if ($aPrefix && $bPrefix) {
            return str_starts_with($a, $b) || str_starts_with($b, $a);
        }
        // one exact, one prefix: the exact text sits under the prefix
        return $aPrefix ? str_starts_with($b, $a) : str_starts_with($a, $b);
    }

    public function create(int $businessId, array $data, ?int $userId = null): SmsAutoResponder
    {
        $userId = $userId ?: Auth::id();

        return SmsAutoResponder::create([
            'ghorfe_online_list_id' => $businessId,
            'name' => $data['name'],
            'trigger_keyword' => trim($data['trigger_keyword']),
            'normalized_keyword' => SmsKeywordNormalizer::normalize($data['trigger_keyword']),
            'match_type' => $data['match_type'],
            'reply_text' => $data['reply_text'],
            'sender_number_id' => $data['sender_number_id'] ?? null,
            'is_active' => (bool) ($data['is_active'] ?? true),
            'created_by' => $userId,
            'updated_by' => $userId,
        ]);
    }

    public function update(SmsAutoResponder $rule, array $data, ?int $userId = null): SmsAutoResponder
    {
        $rule->fill([
            'name' => $data['name'],
            'trigger_keyword' => trim($data['trigger_keyword']),
            'normalized_keyword' => SmsKeywordNormalizer::normalize($data['trigger_keyword']),
            'match_type' => $data['match_type'],
            'reply_text' => $data['reply_text'],
            'sender_number_id' => $data['sender_number_id'] ?? null,
            'is_active' => (bool) ($data['is_active'] ?? false),
            'updated_by' => $userId ?: Auth::id() ?: $rule->updated_by,
        ])->save();

        return $rule;
    }

    /**
     * Flip active state. Re-activating re-runs the collision check.
     *
     * @return string[] errors; empty when toggled
     */
    public function toggle(SmsAutoResponder $rule): array
    {
        if (!$rule->is_active) {
            $errors = $this->validateTrigger($rule->ghorfe_online_list_id, $rule->trigger_keyword, $rule->match_type, $rule->id);
            if ($errors) {
                return $errors;
            }
        }

        $rule->is_active = !$rule->is_active;
        $rule->updated_by = Auth::id() ?: $rule->updated_by;
        $rule->save();

        return [];
    }

    public function delete(SmsAutoResponder $rule): bool
    {
        return (bool) $rule->delete();
    }
}
