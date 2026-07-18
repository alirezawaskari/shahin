<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class AgencyType extends Model implements HasMedia
{
    use SoftDeletes, InteractsWithMedia, \App\Models\Concerns\AdoptsLegacyMedia;

    /** Touch-to-migrate: legacy single-image columns -> Spatie single-file collections. */
    protected function legacyMediaMap(): array
    {
        return ['image' => 'image'];
    }

    /** Touch-to-migrate: legacy gallery rows -> the flat `gallery` collection. */
    protected function legacyGalleryMigrations(): array
    {
        return [
            ['collection' => 'gallery', 'type' => null, 'slotted' => false, 'max' => null],
        ];
    }

    /** Spatie media: single main image + a multi-image gallery. */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('image')->useDisk('public')->singleFile();
        $this->addMediaCollection('gallery')->useDisk('public');
    }

    /** Main image url (shadows the legacy `image` column; disk-checked). */
    public function getImageAttribute(): string
    {
        return \App\Helpers\MediaLibraryHelper::urlIfFileExists($this, 'image')
            ?: ($this->getRawOriginal('image') ? asset($this->getRawOriginal('image')) : '');
    }

    /** Disk-checked gallery image urls (skips media whose file is missing). */
    public function galleryUrls(string $collection = 'gallery')
    {
        return \App\Helpers\MediaLibraryHelper::existingMedia($this, $collection)
            ->map(fn ($m) => $m->getUrl());
    }

    /**
     * @var string[]
     */
    protected $fillable=['user_id','title','en_title','slug','image','total_price','prepayment','content','is_show'];
    /**
     * @var string[]
     */
    protected $dates = ['deleted_at'];

    /**
     * @var array
     */
    protected $guarded = [];

    /**
     * @var array
     */

    public function agencyes()
    {
        return $this->hasMany(agency::class);
    }

    /**
     * @return \string[][]
     */
    public function sluggable(): array
    {
        return ['slug' => ['source' => 'en_title']];
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function galleries()
    {
        return $this->morphMany(Gallery::class, 'galleryable');
    }
}
