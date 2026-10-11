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
        'type' => 'string',
    ];

    public static bool $isSyncing = false;

    protected static function booted(): void
    {
        static::saved(function ($model) {
            if (static::$isSyncing || \App\Models\SmsTemplate::$isSyncing) {
                return;
            }
            static::$isSyncing = true;
            try {
                \App\Models\SmsTemplate::updateOrCreate(
                    ['content' => $model->content],
                    [
                        'name' => $model->title,
                        'fa_name' => $model->title,
                        'status' => $model->status === 'active' ? 1 : 0,
                        'user_id' => $model->created_by ?: auth()->id(),
                    ]
                );
            } catch (\Throwable) {
            } finally {
                static::$isSyncing = false;
            }
        });
    }

    /**
     * Confidentiality is derived purely from the linked group (مالی/حقوقی),
     * exactly like SmsMessage::isConfidential() — never set directly.
     */
    public function isConfidential(): bool
    {
        return (bool) $this->group?->isConfidential();
    }

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
