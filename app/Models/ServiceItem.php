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
    use Sluggable,SoftDeletes,InteractsWithMedia;

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

    public function getImageAttribute(): string
    {
        return $this->getFirstMediaUrl('image');
    }

    public function getIconAttribute(): string
    {
        return $this->getFirstMediaUrl('icon');
    }

    public function getThumbAttribute(): string
    {
        return $this->getFirstMediaUrl('thumb');
    }

    public function getImageAppAttribute(): string
    {
        return $this->getFirstMediaUrl('image_app');
    }

    public function getImgBox1Attribute(): string
    {
        return $this->getFirstMediaUrl('img_box_1');
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
