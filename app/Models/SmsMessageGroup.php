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

        $confidentialKeywords = ['confidential', 'mahrameh', 'hoghoghi', 'ekhtar', 'erja-be-hoghoghi', 'secret', 'private'];
        foreach ($confidentialKeywords as $keyword) {
            if (str_contains($slug, $keyword)) {
                return true;
            }
        }

        $persianKeywords = ['محرمانه', 'حقوقی', 'اخطار', 'ارجاع به حقوقی'];
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
