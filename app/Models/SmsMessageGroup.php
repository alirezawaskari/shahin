<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SmsMessageGroup extends Model
{
    use HasFactory;

    protected $guarded = [];

    /**
     * Top-level subject categories offered to PROMOTIONAL sends (bulk, single in advertising mode):
     * event/greeting, sales and support, with all their subgroups. mali / hoghoghi / ejraei belong
     * to the OTP/system side and are neither offered nor accepted for promotional sends.
     * OTP has its own fixed categories (App\Enums\SmsOtpCategory).
     */
    public const PROMOTIONAL_ROOT_SLUGS = ['monasebati', 'foroush', 'poshtibani'];

    public function business(): BelongsTo
    {
        return $this->belongsTo(GhorfeOnlineList::class, 'ghorfe_online_list_id');
    }

    public function ghorfe(): BelongsTo
    {
        return $this->belongsTo(GhorfeOnlineList::class, 'ghorfe_online_list_id');
    }

    public function getBusinessIdAttribute()
    {
        return $this->ghorfe_online_list_id;
    }

    public function setBusinessIdAttribute($value)
    {
        $this->attributes['ghorfe_online_list_id'] = $value;
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function subgroups(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function templates(): HasMany
    {
        return $this->hasMany(SmsMessageTemplate::class, 'sms_message_group_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(SmsMessage::class, 'sms_message_group_id');
    }

    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }

    /** Root groups that promotional sends may use (their subgroups come along via the relation). */
    public function scopePromotionalRoots($query)
    {
        return $query->whereNull('parent_id')->whereIn('slug', self::PROMOTIONAL_ROOT_SLUGS);
    }

    /** The promotional roots plus every direct subgroup of them. */
    public function scopePromotional($query)
    {
        return $query->where(function ($q) {
            $q->where(fn ($r) => $r->whereNull('parent_id')->whereIn('slug', self::PROMOTIONAL_ROOT_SLUGS))
                ->orWhereIn('parent_id', function ($sub) {
                    $sub->select('id')->from('sms_message_groups')
                        ->whereNull('parent_id')
                        ->whereIn('slug', self::PROMOTIONAL_ROOT_SLUGS);
                });
        });
    }

    /** Whether the group (or its parent) is one of the promotional groups. */
    public function isPromotional(): bool
    {
        if ($this->parent_id) {
            $parent = $this->relationLoaded('parent') ? $this->parent : $this->parent()->first();

            return $parent !== null && $parent->parent_id === null
                && in_array($parent->slug, self::PROMOTIONAL_ROOT_SLUGS, true);
        }

        return in_array($this->slug, self::PROMOTIONAL_ROOT_SLUGS, true);
    }

    /** Whether a group id is acceptable on a promotional send (empty / null always is). */
    public static function isPromotionalId($id): bool
    {
        if ($id === null || $id === '') {
            return true;
        }
        $group = self::find($id);

        return $group !== null && $group->isPromotional();
    }

    public function isConfidential(): bool
    {
        if (isset($this->attributes['is_confidential'])) {
            return (bool) $this->attributes['is_confidential'];
        }

        $slug = strtolower($this->slug ?? '');
        $name = $this->name ?? '';

        // مالی (financial) and حقوقی (legal) are the two confidential top-level
        // categories per task.md §13 (see also §2-4 group taxonomy). Both are
        // matched by slug and by Persian name so subgroups created under
        // either one inherit confidentiality via the parent-chain check below.
        $confidentialKeywords = ['confidential', 'mahrameh', 'hoghoghi', 'ekhtar', 'erja-be-hoghoghi', 'secret', 'private', 'mali', 'financial'];
        $slugParts = preg_split('/[-_]/', $slug) ?: [];
        foreach ($confidentialKeywords as $keyword) {
            if (in_array($keyword, $slugParts, true) || $slug === $keyword) {
                return true;
            }
        }

        $persianKeywords = ['محرمانه', 'حقوقی', 'اخطار', 'ارجاع به حقوقی', 'مالی'];
        foreach ($persianKeywords as $keyword) {
            if (str_contains($name, $keyword)) {
                return true;
            }
        }

        if ($this->parent_id) {
            $parent = $this->relationLoaded('parent') ? $this->parent : $this->parent()->first();
            if ($parent && $parent->isConfidential()) {
                return true;
            }
        }

        return false;
    }
}
