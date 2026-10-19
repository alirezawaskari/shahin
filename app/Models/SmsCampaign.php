<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SmsCampaign extends Model
{
    use HasFactory;

    /** Unsent compose-page content parked in the «صف ارسال»: no recipients rows, no debit, no send. */
    public const STATUS_PENDING_QUEUE = 'pending_queue';

    protected $guarded = [];

    protected $casts = [
        'filters' => 'array',
        'scheduled_at' => 'datetime',
        'cost_estimate' => 'decimal:2',
        'total_recipients' => 'integer',
        'sent_count' => 'integer',
        'success_count' => 'integer',
        'failed_count' => 'integer',
        'pending_count' => 'integer',
    ];

    public function scopePendingQueue($query)
    {
        return $query->where('status', self::STATUS_PENDING_QUEUE);
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(GhorfeOnlineList::class, 'ghorfe_online_list_id');
    }

    public function ghorfe(): BelongsTo
    {
        return $this->belongsTo(GhorfeOnlineList::class, 'ghorfe_online_list_id');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(SmsMessageTemplate::class, 'sms_message_template_id');
    }

    public function senderNumber(): BelongsTo
    {
        return $this->belongsTo(SenderNumber::class, 'sender_number_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function recipients(): HasMany
    {
        return $this->hasMany(SmsCampaignRecipient::class, 'sms_campaign_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(SmsMessage::class, 'sms_campaign_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(SmsTransaction::class, 'sms_campaign_id');
    }
}
