<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SmsContractOtp extends Model
{
    protected $guarded = [];

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
        'attempts' => 'integer',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(GhorfeOnlineList::class, 'ghorfe_online_list_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
