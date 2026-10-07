<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SmsMessageTemplateHistory extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function template(): BelongsTo
    {
        return $this->belongsTo(SmsMessageTemplate::class, 'sms_message_template_id');
    }

    public function changer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
