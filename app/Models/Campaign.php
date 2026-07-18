<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Campaign extends Model implements HasMedia
{
    use InteractsWithMedia, \App\Models\Concerns\AdoptsLegacyMedia;

    /** Touch-to-migrate: legacy single-image columns -> Spatie single-file collections. */
    protected function legacyMediaMap(): array
    {
        return ['image' => 'image', 'image_header' => 'image_header'];
    }

    protected $fillable = [
        'name',
        'subject',
        'keyword',
        'capacity',
        'usage',
        'content',
        'image',
        'image_header',
        'slug',
        'form',
        'start_date',
        'end_date',
    ];

    /**
     * Spatie media collections. Legacy path columns (image, image_header) are kept,
     * but reads route through the accessors below (Spatie first, legacy column fallback).
     * `company_logos` holds the collaboration company logos (multi-file).
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('image')->useDisk('public')->singleFile();
        $this->addMediaCollection('image_header')->useDisk('public')->singleFile();
        $this->addMediaCollection('company_logos')->useDisk('public');
    }

    /** Main image url (shadows the legacy `image` column; disk-checked, legacy fallback). */
    public function getImageAttribute(): string
    {
        return \App\Helpers\MediaLibraryHelper::urlIfFileExists($this, 'image')
            ?: ($this->getRawOriginal('image') ? asset($this->getRawOriginal('image')) : '');
    }

    /** Header image url (shadows the legacy `image_header` column; disk-checked, legacy fallback). */
    public function getImageHeaderAttribute(): string
    {
        return \App\Helpers\MediaLibraryHelper::urlIfFileExists($this, 'image_header')
            ?: ($this->getRawOriginal('image_header') ? asset($this->getRawOriginal('image_header')) : '');
    }

    public function campaign_collaboration() : HasMany
    {
        return $this->hasMany(CampaignCollaboration::class,'campaign_id','id',);
    }

    public function scopeAvailable($query)
    {
        return $query->where(function ($q) {
            $q->whereNotNull('start_date')
                ->whereNotNull('end_date')
                ->whereDate('start_date', '<=', now())
                ->whereDate('end_date', '>=', now())
                ->where('usage', '>', 0);
        });
    }
}
