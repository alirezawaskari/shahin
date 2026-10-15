<?php

namespace App\Models\Concerns;

use App\Models\AdvertisingBalance;
use App\Models\BusinessSenderNumber;
use App\Models\OtpBalance;
use App\Models\SenderNumber;
use App\Models\SmsCampaign;
use App\Models\SmsMessage;
use App\Models\SmsTransaction;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Trait providing SMS and sender-number relationships for tenant entities (GhorfeOnlineList).
 */
trait HasSenderNumbers
{
    /**
     * Sender numbers assigned to this business through the pivot.
     */
    public function senderNumbers(): BelongsToMany
    {
        return $this->belongsToMany(
            SenderNumber::class,
            'business_sender_number',
            'ghorfe_online_list_id',
            'sender_number_id'
        )
        ->using(BusinessSenderNumber::class)
        ->withPivot('assigned_at')
        ->withTimestamps();
    }

    /**
     * Active advertising sender numbers assigned to this business.
     */
    public function advertisingSenderNumbers(): BelongsToMany
    {
        return $this->senderNumbers()
            ->where('sender_numbers.type', 'advertising')
            ->where('sender_numbers.status', 'active');
    }

    /**
     * The OTP financial balance track for this business.
     */
    public function otpBalance(): HasOne
    {
        return $this->hasOne(OtpBalance::class, 'ghorfe_online_list_id');
    }

    /**
     * The advertising financial balance for this business. Shared by all of its advertising lines.
     */
    public function advertisingBalance(): HasOne
    {
        return $this->hasOne(AdvertisingBalance::class, 'ghorfe_online_list_id');
    }

    /**
     * SMS messages logged for this business.
     */
    public function smsMessages(): HasMany
    {
        return $this->hasMany(SmsMessage::class, 'ghorfe_online_list_id');
    }

    /**
     * SMS financial transactions logged for this business.
     */
    public function smsTransactions(): HasMany
    {
        return $this->hasMany(SmsTransaction::class, 'ghorfe_online_list_id');
    }

    /**
     * Bulk / AI SMS campaigns for this business.
     */
    public function smsCampaigns(): HasMany
    {
        return $this->hasMany(SmsCampaign::class, 'ghorfe_online_list_id');
    }
}
