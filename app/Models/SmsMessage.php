<?php

namespace App\Models;

use App\Observers\SmsMessageObserver;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SmsMessage extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::observe(SmsMessageObserver::class);
    }

    protected $guarded = [];

    protected $casts = [
        'delivered_at' => 'datetime',
        'cost' => 'decimal:2',
        'character_count' => 'integer',
        'page_count' => 'integer',
        'is_bulk' => 'boolean',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(GhorfeOnlineList::class, 'ghorfe_online_list_id');
    }

    public function ghorfe(): BelongsTo
    {
        return $this->belongsTo(GhorfeOnlineList::class, 'ghorfe_online_list_id');
    }

    public function senderNumber(): BelongsTo
    {
        return $this->belongsTo(SenderNumber::class, 'sender_number_id');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(SmsMessageTemplate::class, 'sms_message_template_id');
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(SmsMessageGroup::class, 'sms_message_group_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(Request::class, 'request_id');
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(SmsCampaign::class, 'sms_campaign_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(SmsTransaction::class, 'sms_message_id');
    }

    public function scopeOutbound($query)
    {
        return $query->where('direction', 'outbound');
    }

    public function scopeInbound($query)
    {
        return $query->where('direction', 'inbound');
    }

    public function scopeOtp($query)
    {
        return $query->where('service_type', 'otp');
    }

    public function scopeAdvertising($query)
    {
        return $query->where('service_type', 'advertising');
    }

    public function scopeNormal($query)
    {
        return $query->where('service_type', 'normal');
    }

    public function isConfidential(): bool
    {
        if (($this->classification ?? '') === 'classified') {
            return true;
        }

        return (bool) $this->group?->isConfidential();
    }
}
