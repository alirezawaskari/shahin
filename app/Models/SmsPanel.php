<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SmsPanel extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'connection_info' => 'array',
        'send_settings' => 'array',
        'receive_settings' => 'array',
        'delivery_settings' => 'array',
        'status' => 'string',
        'type' => 'string',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(GhorfeOnlineList::class, 'ghorfe_online_list_id');
    }

    public function senderNumbers(): HasMany
    {
        return $this->hasMany(SenderNumber::class, 'sms_panel', 'name');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeOtp($query)
    {
        return $query->whereIn('type', ['otp', 'all']);
    }

    public function scopeAdvertising($query)
    {
        return $query->whereIn('type', ['normal-advertising', 'advertising', 'all']);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isOtp(): bool
    {
        return in_array($this->type, ['otp', 'all'], true);
    }

    public function isAdvertising(): bool
    {
        return in_array($this->type, ['normal-advertising', 'advertising', 'all'], true);
    }

    public function getConnectionSetting(string $key, mixed $default = null): mixed
    {
        return $this->connection_info[$key] ?? $default;
    }

    public function getSendSetting(string $key, mixed $default = null): mixed
    {
        return $this->send_settings[$key] ?? $default;
    }

    public function getReceiveSetting(string $key, mixed $default = null): mixed
    {
        return $this->receive_settings[$key] ?? $default;
    }

    public function getDeliverySetting(string $key, mixed $default = null): mixed
    {
        return $this->delivery_settings[$key] ?? $default;
    }
}
