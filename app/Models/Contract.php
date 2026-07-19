<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Contract extends Model implements HasMedia
{
    use SoftDeletes, InteractsWithMedia, \App\Models\Concerns\AdoptsLegacyMedia;

    /** Touch-to-migrate: legacy single-image columns -> Spatie single-file collections. */
    protected function legacyMediaMap(): array
    {
        return ['file' => 'file'];
    }

    /**
     * @var string[]
     */
    protected $dates = ['deleted_at'];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('file')->useDisk('public')->singleFile();
    }

    public function getFileAttribute(): string
    {
        return \App\Helpers\MediaLibraryHelper::urlIfFileExists($this, 'file')
            ?: ($this->getRawOriginal('file') ? asset($this->getRawOriginal('file')) : '');
    }

    /**
     * @var string[]
     */
    protected $fillable=['type','title','content','file','status'];

    /**
     * list of status of contract
     */
    const STATUS = [
        '1' => 'منتظر تایید وکیل',
        '2' => 'منتظر تایید مدیر مالی',
        '3' => 'منتظر تایید مدیر عامل'
    ];

    /**
     * list of types  of contract
     */
    const TYPE = [
        '1' => 'خرید',
        '2' => 'فروش',
        '3' => 'واگذاری'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function contractHistories()
    {
        return $this->hasMany(ContractHistory::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function arrangeContracts()
    {
        return $this->hasMany(ArrangeContract::class);
    }

}
