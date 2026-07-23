<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class TicketHistory extends Model implements HasMedia
{
    use InteractsWithMedia, \App\Models\Concerns\AdoptsLegacyMedia;

    /** Touch-to-migrate: legacy paths inside the `file` JSON array -> `file` collection. */
    protected function legacyArrayMediaMap(): array
    {
        return ['file' => 'file'];
    }

    /** @see Ticket::fileUrls() — same union (Spatie media + un-adopted legacy paths). */
    public function fileUrls(): array
    {
        return $this->arrayMediaUrls('file', 'file');
    }

    protected $fillable = [
        'ticket_id',
        'user_id',
        'type',
        'content',
        'file'
    ];
    protected $casts = [
        'file' => 'array',
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('file')->useDisk('public');
    }
    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
