<?php

namespace App\Models;

use DomainException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SenderNumber extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'type' => 'string',
        'status' => 'string',
    ];

    protected static function booted(): void
    {
        static::saving(function (SenderNumber $senderNumber) {
            if ($senderNumber->type === 'otp') {
                $query = static::query()->where('type', 'otp');
                if ($senderNumber->exists) {
                    $query->where($senderNumber->getKeyName(), '!=', $senderNumber->getKey());
                }
                if ($query->exists()) {
                    throw new DomainException('Only one OTP sender number is allowed across the entire system.');
                }
            }
        });
    }

    public function businesses(): BelongsToMany
    {
        return $this->belongsToMany(
            GhorfeOnlineList::class,
            'business_sender_number',
            'sender_number_id',
            'ghorfe_online_list_id'
        )
        ->using(BusinessSenderNumber::class)
        ->withPivot('assigned_at')
        ->withTimestamps();
    }

    public function advertisingBalances(): HasMany
    {
        return $this->hasMany(AdvertisingBalance::class, 'sender_number_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(SmsMessage::class, 'sender_number_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(SmsTransaction::class, 'sender_number_id');
    }

    public function campaigns(): HasMany
    {
        return $this->hasMany(SmsCampaign::class, 'sender_number_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeOtp($query)
    {
        return $query->where('type', 'otp');
    }

    public function scopeAdvertising($query)
    {
        return $query->where('type', 'advertising');
    }

    public function isOtp(): bool
    {
        return $this->type === 'otp';
    }

    public function isAdvertising(): bool
    {
        return $this->type === 'advertising';
    }
}
