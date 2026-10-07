<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SmsTransaction extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'charge_amount' => 'decimal:2',
        'consume_amount' => 'decimal:2',
        'balance_before' => 'decimal:2',
        'balance_after' => 'decimal:2',
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

    public function message(): BelongsTo
    {
        return $this->belongsTo(SmsMessage::class, 'sms_message_id');
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(SmsCampaign::class, 'sms_campaign_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeForOtp($query)
    {
        return $query->where('credit_type', 'otp');
    }

    public function scopeForAdvertising($query)
    {
        return $query->where('credit_type', 'advertising');
    }
}
