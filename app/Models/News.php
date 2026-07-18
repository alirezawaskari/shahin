<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Cviebrock\EloquentSluggable\Sluggable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;


class News extends Model implements HasMedia
{
    use Sluggable , SoftDeletes, InteractsWithMedia, \App\Models\Concerns\AdoptsLegacyMedia;
//    use Taggable ;

    /** Touch-to-migrate: legacy single-image columns -> Spatie single-file collections. */
    protected function legacyMediaMap(): array
    {
        return ['image' => 'image'];
    }

    /**
     * @var string[]
     */
    protected $dates = ['deleted_at'];
    /**
     * @var string[]
     */
    protected $fillable=['user_id','category_news_id','title','subject','slug','image','description','status'];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('image')->useDisk('public')->singleFile();
    }

    public function getImageAttribute(): string
    {
        return \App\Helpers\MediaLibraryHelper::urlIfFileExists($this, 'image')
            ?: ($this->getRawOriginal('image') ? asset($this->getRawOriginal('image')) : '');
    }

    /**
     * @return \string[][]
     */
    public function sluggable(): array{  return ['slug' => ['source' => 'title']];}

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
