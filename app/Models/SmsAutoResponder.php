<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SmsAutoResponder extends Model
{
    use SoftDeletes;

    public const MATCH_EXACT = 'exact';
    public const MATCH_STARTS_WITH = 'starts_with';

    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(GhorfeOnlineList::class, 'ghorfe_online_list_id');
    }

    public function senderNumber(): BelongsTo
    {
        return $this->belongsTo(SenderNumber::class, 'sender_number_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForBusiness($query, int $businessId)
    {
        return $query->where('ghorfe_online_list_id', $businessId);
    }

    public static function matchTypeLabel(string $type): string
    {
        return $type === self::MATCH_STARTS_WITH ? 'شروع با' : 'مطابقت کامل';
    }
}
