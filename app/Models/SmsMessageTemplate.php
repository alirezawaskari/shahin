<?php

namespace App\Models;

use App\Observers\SmsMessageTemplateObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ObservedBy([SmsMessageTemplateObserver::class])]
class SmsMessageTemplate extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'service_type' => 'string',
        'status' => 'string',
    ];

    protected static function booted(): void
    {
        // Observer registered via #[ObservedBy([SmsMessageTemplateObserver::class])]
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(SmsMessageGroup::class, 'sms_message_group_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function histories(): HasMany
    {
        return $this->hasMany(SmsMessageTemplateHistory::class, 'sms_message_template_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(SmsMessage::class, 'sms_message_template_id');
    }

    public function campaigns(): HasMany
    {
        return $this->hasMany(SmsCampaign::class, 'sms_message_template_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeForOtp($query)
    {
        return $query->where('service_type', 'otp');
    }

    public function scopeForAdvertising($query)
    {
        return $query->where('service_type', 'advertising');
    }

    public function scopeForNormal($query)
    {
        return $query->where('service_type', 'normal');
    }
}
