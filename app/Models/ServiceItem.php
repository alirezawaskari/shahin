<?php

namespace App\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class ServiceItem extends Model implements AuditableContract, HasMedia
{
    use Auditable;
    use Sluggable,SoftDeletes,InteractsWithMedia,\App\Models\Concerns\AdoptsLegacyMedia;

    /** Touch-to-migrate: legacy single-image columns -> Spatie single-file collections. */
    protected function legacyMediaMap(): array
    {
        return [
            'image'     => 'image',
            'icon'      => 'icon',
            'thumb'     => 'thumb',
            'image_app' => 'image_app',
            'img_box_1' => 'img_box_1',
        ];
    }

    /** Single-file media collections owned directly by the service item (Spatie native). */
    const MEDIA_FIELDS = ['image', 'icon', 'thumb', 'image_app', 'img_box_1'];

    /** Multi-image gallery collections (data-mining / homepage banner). */
    const GALLERY_COLLECTIONS = [
        'gallery_horizontal' => 4,
        'gallery_vertical'   => 2,
    ];

    public function registerMediaCollections(): void
    {
        foreach (self::MEDIA_FIELDS as $field) {
            $this->addMediaCollection($field)->useDisk('public')->singleFile();
        }
        foreach (array_keys(self::GALLERY_COLLECTIONS) as $collection) {
            $this->addMediaCollection($collection)->useDisk('public');
        }
        // portfolio / work-samples: unlimited multi-image collection
        $this->addMediaCollection('portfolio')->useDisk('public');
    }

    /** The media occupying gallery slot $index in $collection, if any. */
    public function gallerySlot(string $collection, int $index)
    {
        return $this->getMedia($collection)
            ->first(fn ($m) => (int) $m->getCustomProperty('index') === $index);
    }

    /**
     * Disk-checked URLs of a multi-image gallery collection (gallery_horizontal,
     * gallery_vertical, portfolio) — media whose file is missing on disk is skipped,
     * mirroring the single-image accessors. For display surfaces; edit/management
     * views should keep iterating getMedia() so broken rows stay removable.
     */
    public function galleryUrls(string $collection)
    {
        return \App\Helpers\MediaLibraryHelper::existingMedia($this, $collection)
            ->map(fn ($m) => $m->getUrl());
    }

    public function getImageAttribute(): string
    {
        return \App\Helpers\MediaLibraryHelper::urlIfFileExists($this, 'image')
            ?: ($this->getRawOriginal('image') ? asset($this->getRawOriginal('image')) : '');
    }

    public function getIconAttribute(): string
    {
        return \App\Helpers\MediaLibraryHelper::urlIfFileExists($this, 'icon')
            ?: ($this->getRawOriginal('icon') ? asset($this->getRawOriginal('icon')) : '');
    }

    public function getThumbAttribute(): string
    {
        return \App\Helpers\MediaLibraryHelper::urlIfFileExists($this, 'thumb')
            ?: ($this->getRawOriginal('thumb') ? asset($this->getRawOriginal('thumb')) : '');
    }

    public function getImageAppAttribute(): string
    {
        return \App\Helpers\MediaLibraryHelper::urlIfFileExists($this, 'image_app')
            ?: ($this->getRawOriginal('image_app') ? asset($this->getRawOriginal('image_app')) : '');
    }

    public function getImgBox1Attribute(): string
    {
        return \App\Helpers\MediaLibraryHelper::urlIfFileExists($this, 'img_box_1')
            ?: ($this->getRawOriginal('img_box_1') ? asset($this->getRawOriginal('img_box_1')) : '');
    }

    /**
     * @var string[]
     */
    protected $fillable=['service_id','title','en_title','description','slug','status','isShow',
        'content','image','icon','thumb','image_app','img_box_1','txt_box_1','left_content'];
    /**
     * @var string[]
     */
    protected $dates = ['deleted_at'];
    /**
     * @var array
     */
    protected $guarded = [];
   /**
     * @return \string[][]
     */
    public function sluggable():array
    {
        return [
            'slug' => [
                'source' => 'en_title',
                'onUpdate' => true,
            ]
        ];
    }
    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }
    /**
     * @return string
     */
    public function path()
    {
        return "/serviceItem/$this->slug";
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function askedQuestions()
    {
        return $this->morphMany(AskedQuestion::class, 'askedquestionable');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function workSamples()
    {
        return $this->morphMany(WorkSample::class, 'worksampleable');
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function galleries()
    {
        return $this->morphMany(Gallery::class, 'galleryable');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function menu()
    {
        return $this->morphMany(Menu::class, 'menuable');
    }


}
