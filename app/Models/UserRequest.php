<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserRequest extends Model
{
    use SoftDeletes;

    /**
     * @var array
     */
    protected $guarded = [];

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function smsMessages()
    {
        return $this->hasMany(\App\Models\SmsMessage::class, 'request_id');
    }

    public function histories()
    {
        return $this->morphMany(\App\Models\History::class, 'historiable');
    }
}
