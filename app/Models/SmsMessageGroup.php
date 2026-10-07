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
}
