<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class WorkSample extends Model implements HasMedia
{
    use SoftDeletes, InteractsWithMedia, \App\Models\Concerns\AdoptsLegacyMedia;

    /** Touch-to-migrate: the legacy `image` path column -> the `image` collection. */
    protected function legacyMediaMap(): array
    {
        return ['image' => 'image'];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('image')->useDisk('public')->singleFile();
    }

    /**
     * Image url (shadows the legacy `image` column; disk-checked, legacy fallback).
     *
     * NOTE for the Service/ServiceItem portfolio migration: this accessor returns an
     * ABSOLUTE url, so AdoptsLegacyMedia's work-sample readers deliberately go through
     * getRawOriginal('image') — they need the raw relative legacy path to copy from.
     */
    public function getImageAttribute(): string
    {
        return \App\Helpers\MediaLibraryHelper::urlIfFileExists($this, 'image')
            ?: ($this->getRawOriginal('image') ? asset($this->getRawOriginal('image')) : '');
    }

    /**
     * @var string[]
     */
    protected $fillable=['worksampleable_id','worksampleable_type','title','image','status', 'center_id'];

    /**
     * @var string[]
     */
    protected $dates = ['deleted_at'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function worksampleable()
    {
        return $this->morphTo();
    }
    public function center()
    {
        return $this->belongsTo(Center::class);
    }
}
