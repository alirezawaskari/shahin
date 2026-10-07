<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class BusinessSenderNumber extends Pivot
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'business_sender_number';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true;

    protected $guarded = [];

    protected $casts = [
        'assigned_at' => 'datetime',
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
}
