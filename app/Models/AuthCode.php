<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AuthCode extends Model
{
    use HasFactory;

    /**
     * @var string
     */
    protected $table = 'auth_code';
    /**
     * @var string[]
     */
    protected $fillable = ['code', 'phone', 'expiration_time', 'time_code_again'];

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
        'attempts' => 'integer',
    ];

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function isUsed(): bool
    {
        return $this->used_at !== null;
    }

    public function isActive(): bool
    {
        return !$this->isUsed() && !$this->isExpired();
    }

    public function hasExceededAttempts(int $maxAttempts): bool
    {
        return $this->attempts >= $maxAttempts;
    }

    /**
     * @return int
     */
    public function secondsUntilExpiration(): int
    {
        if ($this->expires_at === null) {
            return 0;
        }

        return max(0, (int) Carbon::now()->diffInSeconds($this->expires_at, false));
    }

    public function secondsSinceCreated(): int
    {
        if ($this->created_at === null) {
            return PHP_INT_MAX;
        }

        return (int) $this->created_at->diffInSeconds(Carbon::now());
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query
            ->whereNull('used_at')
            ->where('expires_at', '>', Carbon::now());
    }

    public function scopeForPhone(Builder $query, string $phone): Builder
    {
        return $query->where('phone', $phone);
    }



}
