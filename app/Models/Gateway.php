<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Gateway extends Model implements HasMedia
{
    use InteractsWithMedia, \App\Models\Concerns\AdoptsLegacyMedia;

    /** Touch-to-migrate: legacy single-image columns -> Spatie single-file collections. */
    protected function legacyMediaMap(): array
    {
        return ['logo' => 'logo'];
    }

    protected $table = 'gateways';
    protected $fillable = [
        'name',
        'fa_name',
        'logo',
        'status'
    ];
    protected $appends = ['status_label'];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('logo')->useDisk('public')->singleFile();
    }

    public function getLogoAttribute(): string
    {
        return \App\Helpers\MediaLibraryHelper::urlIfFileExists($this, 'logo')
            ?: ($this->getRawOriginal('logo') ? asset($this->getRawOriginal('logo')) : '');
    }

    public function getStatusLabelAttribute()
    {
        return $this->status ? 'فعال' : 'غیرفعال';
    }
}
