<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Slider extends Model implements HasMedia
{
    use SoftDeletes, InteractsWithMedia;

    /**
     * @var string[]
     */
    protected $dates = ['deleted_at'];
    /**
     * @var string[]
     */
    protected $fillable=['img','title','link','type','category_id','representation_id'];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('img')->useDisk('public')->singleFile();
    }

    public function getImgAttribute(): string
    {
        return \App\Helpers\MediaLibraryHelper::urlIfFileExists($this, 'img');
    }
    const TYPE = [
        '0' => 'pc',
        '4' => 'موبایل',
    ];
    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
    */
    public function sliderable()
    {
        return $this->morphTo();
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function representation()
    {
        return $this->belongsTo(Representation::class);
    }
      /**
     * @param $query
     * @return mixed
     */
    public function scopeFilter($query)
    {
        $query->where('sliderable_id' , null);

        return $query;
    }
}
