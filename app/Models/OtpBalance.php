<?php

namespace App\Models;

use App\Models\Concerns\DebitsBalance;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OtpBalance extends Model
{
    use HasFactory, DebitsBalance;

    /**
     * Balance is deliberately not mass-assignable: it may only change through
     * DebitsBalance::lockAndDebit() or the explicit balance-adjustment services.
     */
    protected $fillable = ['ghorfe_online_list_id', 'low_balance_threshold'];

    protected $casts = [
        'balance' => 'decimal:2',
        'low_balance_threshold' => 'decimal:2',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(GhorfeOnlineList::class, 'ghorfe_online_list_id');
    }

    public function ghorfe(): BelongsTo
    {
        return $this->belongsTo(GhorfeOnlineList::class, 'ghorfe_online_list_id');
    }

    public function isLowBalance(): bool
    {
        return (float) $this->balance <= (float) $this->low_balance_threshold;
    }

    public function hasSufficientBalance(float $amount): bool
    {
        return (float) $this->balance >= $amount;
    }
}
