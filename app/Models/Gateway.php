<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Gateway extends Model implements HasMedia
{
    use InteractsWithMedia;

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
        return \App\Helpers\MediaLibraryHelper::urlIfFileExists($this, 'logo');
    }

    public function getStatusLabelAttribute()
    {
        return $this->status ? 'فعال' : 'غیرفعال';
    }
}
