<?php

namespace App\Models;

use App\Models\Concerns\DebitsBalance;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdvertisingBalance extends Model
{
    use HasFactory, DebitsBalance;

    protected $guarded = [];

    protected $casts = [
        'balance' => 'decimal:2',
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

    public function hasSufficientBalance(float $amount): bool
    {
        return (float) $this->balance >= $amount;
    }
}
