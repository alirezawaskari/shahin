<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmsLog extends Model
{
    protected $guarded = [];

    public function business()
    {
        return $this->belongsTo(GhorfeOnlineList::class, 'ghorfe_online_list_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function message()
    {
        return $this->belongsTo(Groupsms::class, 'sms_message_id');
    }

    public function campaign()
    {
        return $this->belongsTo(Groupsms::class, 'sms_campaign_id');
    }

    public const STATUS_PENDING = 'pending';
    public const STATUS_PAID = 'paid';
    public const STATUS_FAILED = 'failed';
    public const STATUS_CANCELLED = 'cancelled';

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isPaid(): bool
    {
        return $this->status === self::STATUS_PAID;
    }

    public function getAmountAttribute()
    {
        return (float) ($this->attributes['charge_amount'] ?? $this->attributes['consume_amount'] ?? 0);
    }

    public function setAmountAttribute($val)
    {
        $this->attributes['charge_amount'] = $val;
    }

    public function getRefNumberAttribute()
    {
        return $this->attributes['tracking_id'] ?? null;
    }

    public function setRefNumberAttribute($val)
    {
        $this->attributes['tracking_id'] = $val;
    }

    public function getAuthorityAttribute()
    {
        return $this->attributes['tracking_id'] ?? null;
    }

    public function setAuthorityAttribute($val)
    {
        $this->attributes['tracking_id'] = $val;
    }

    public function getReturnUrlAttribute()
    {
        $data = json_decode($this->attributes['response'] ?? '', true);
        return $data['return_url'] ?? null;
    }

    public function setReturnUrlAttribute($val)
    {
        $data = json_decode($this->attributes['response'] ?? '', true) ?: [];
        $data['return_url'] = $val;
        $this->attributes['response'] = json_encode($data);
    }

    public function getSenderIdAttribute()
    {
        return $this->attributes['user_id'] ?? $this->attributes['created_by'] ?? null;
    }

    public function getSenderRoleAttribute()
    {
        $data = json_decode($this->attributes['response'] ?? '', true);
        return $data['sender_role'] ?? null;
    }

    public function getChannelAttribute()
    {
        $data = json_decode($this->attributes['response'] ?? '', true);
        return $data['channel'] ?? (str_starts_with($this->type ?? '', 'audit_') ? substr($this->type, 6) : null);
    }

    public function getSentAtAttribute()
    {
        return $this->created_at;
    }

    public function getCallcenterIdAttribute()
    {
        $data = json_decode($this->attributes['response'] ?? '', true);
        return $data['callcenter_id'] ?? null;
    }

    public function getRequestIdAttribute()
    {
        $data = json_decode($this->attributes['response'] ?? '', true);
        return $data['request_id'] ?? null;
    }
}
