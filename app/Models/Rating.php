<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A visitor's star rating + comment on a branch (Center) or a partner (Employee).
 *
 * Written by the public API, never by the admin — the admin panel only moderates. Nothing
 * reaches a frontend before an admin approves it, so status defaults to PENDING.
 */
class Rating extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'score'  => 'integer',
        'status' => 'integer',
    ];

    const PENDING  = 0;
    const APPROVED = 1;
    const REJECTED = 2;

    const STATUS = [
        self::PENDING  => 'در انتظار تایید',
        self::APPROVED => 'تایید شده',
        self::REJECTED => 'رد شده',
    ];

    /** The Center or Employee being rated. */
    public function rateable()
    {
        return $this->morphTo();
    }

    /** Set when the reviewer was logged in; a guest review keeps only name/family. */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /** Reviewer's display name — the stored name, or the linked user's, or "کاربر مهمان". */
    public function getFullNameAttribute(): string
    {
        $name = trim($this->name . ' ' . $this->family);

        if ($name !== '') {
            return $name;
        }

        return $this->user ? trim($this->user->name . ' ' . $this->user->family) : 'کاربر مهمان';
    }

    /** Only what a frontend may show. */
    public function scopeApproved($query)
    {
        return $query->where('status', self::APPROVED);
    }
}
