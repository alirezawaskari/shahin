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
