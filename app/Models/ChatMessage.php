<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'laleh_ai_id',
        'user_id',
        'message',
        'is_user_message',
    ];

    /**
     * پیام مربوط به کدام مکالمه است.
     */
    public function lalehAi()
    {
        return $this->belongsTo(LalehAi::class);
    }

    /**
     * پیام مربوط به کدام کاربر است (ممکن است null باشد اگر کاربر مهمان).
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
