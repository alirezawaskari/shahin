<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * A timed «شگفت‌انگیز» batch: its products leave the offer at `ends_at`.
 *
 * The product API owns how these are served (HomeService::getSuggestedItems reads active,
 * in-schedule groups); the panel creates one group per «افزودن به شگفت‌انگیز» submission
 * of a business, so each selection can carry its own end time.
 */
class SpecialSaleGroup extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = [
        'is_active' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at'   => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $group) {
            if (empty($group->slug)) {
                // slug is unique, and every submission makes a group
                $group->slug = (Str::slug($group->title) ?: 'special-sale') . '-' . Str::lower(Str::random(8));
            }
        });
    }

    public function items()
    {
        return $this->hasMany(SpecialSaleItem::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /** Groups whose window has not closed yet (the API treats a null end as open-ended). */
    public function scopeNotEnded(Builder $query): Builder
    {
        return $query->where(fn (Builder $q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', now()));
    }
}
