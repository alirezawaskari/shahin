<?php

namespace App\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class GhorfeOnlineList extends Model implements HasMedia
{
    use SoftDeletes , Sluggable, InteractsWithMedia;

    /**
     * @var array
     */
    protected $guarded = [];


    /**
     *types of ghorfeOnline
     */
    const TYPE = [
        'service' => 'ارائه خدمات',
        'product' => 'فروش کالا',
        'all' => 'ارائه خدمات و فروش کالا',
    ];
    /**
     * @var string[]
     */
    protected $casts = [
        'sms_code' => 'array',
        'footer_tag' => 'array'
    ];
    /**
     * Return the sluggable configuration array for this model.
     *
     * @return array
     */
    public function sluggable():array
    {
        return [
            'slug' => [
                'source' => 'en_title'
            ]
        ];
    }
    /**
     * Summary of getActiveDomainName
     * @return string
     */
    public static function getActiveDomainName() {
        $host = request()->getHost();

        $parts = explode( '.', $host );

        if ( count( $parts ) > 2 ) {
            $domain = implode( '.', array_slice( $parts, -2 ) );
        } else {
            $domain = $host;
        }

        return $domain;
    }
    /**
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function template()
    {
        return $this->belongsTo(Template::class, 'template_id');
    }
    public function galleries()
    {
        return $this->morphMany(Gallery::class, 'galleryable');
    }

    /** Slot-based data-mining homepage gallery (4 small + 1 big, desktop + mobile). */
    const GALLERY_COLLECTIONS = ['gallery_desktop', 'gallery_mobile'];

    /** Single-file settings images (legacy *_id columns kept, read through accessors). */
    const SINGLE_IMAGE_COLLECTIONS = ['logo', 'footer_logo', 'fav', 'mohr', 'addvertisment_slider'];

    public function registerMediaCollections(): void
    {
        foreach (self::GALLERY_COLLECTIONS as $collection) {
            $this->addMediaCollection($collection)->useDisk('public');
        }
        foreach (self::SINGLE_IMAGE_COLLECTIONS as $collection) {
            $this->addMediaCollection($collection)->useDisk('public')->singleFile();
        }
    }

    /** The media occupying gallery slot $index in $collection, if any. */
    public function gallerySlot(string $collection, int $index)
    {
        return $this->getMedia($collection)
            ->first(fn ($m) => (int) $m->getCustomProperty('index') === $index);
    }

    /** Disk-checked URLs of a gallery collection (skips media whose file is missing). */
    public function galleryUrls(string $collection)
    {
        return \App\Helpers\MediaLibraryHelper::existingMedia($this, $collection)
            ->map(fn ($m) => $m->getUrl());
    }

    /** Settings-image url accessors (shadow the legacy *_id columns; disk-checked). */
    public function getLogoAttribute(): string
    {
        return \App\Helpers\MediaLibraryHelper::urlIfFileExists($this, 'logo');
    }

    public function getFooterLogoAttribute(): string
    {
        return \App\Helpers\MediaLibraryHelper::urlIfFileExists($this, 'footer_logo');
    }

    public function getFavAttribute(): string
    {
        return \App\Helpers\MediaLibraryHelper::urlIfFileExists($this, 'fav');
    }

    public function getMohrAttribute(): string
    {
        return \App\Helpers\MediaLibraryHelper::urlIfFileExists($this, 'mohr');
    }

    public function getAddvertismentSliderAttribute(): string
    {
        return \App\Helpers\MediaLibraryHelper::urlIfFileExists($this, 'addvertisment_slider');
    }

    public function theme()
    {
        return $this->belongsTo(Theme::class);
    }
    public function shahinPackage()
    {
        return $this->belongsTo(ShahinPackage::class, 'shahin_package_id');
    }

    /**
     * @return string
     */
    public function path()
    {
        return "/$this->slug";
    }

    public function settings()
    {
        return $this->morphMany(Setting::class, 'settingable');
    }
    public function ghorfeCategories()
    {
        return $this->belongsToMany(CategoryPermission::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function services()
    {
        return $this->belongsToMany(Service::class);
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function products()
    {
        return $this->belongsToMany(Product::class);
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
    */
    public function financialPlansTypes()
    {
        return $this->belongsToMany(FinancialPlansType::class);
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
    */
    public function factors()
    {
        return $this->belongsToMany(Factor::class);
    }
     /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function menus()
    {
        return $this->hasMany(\App\Menu::class, 'ghorfe_online_list_id', 'id');
    }

     /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function callcenters()
    {
        return $this->belongsToMany(Callcenter::class);
    }
     /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function articles()
    {
        return $this->morphMany(Article::class , 'articleable');
    }
     /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function tickets()
    {
        return $this->morphMany(Ticket::class , 'ticketable');
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function sliders()
    {
        return $this->morphMany(Slider::class , 'sliderable');
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function about()
    {
        return $this->hasOne(About::class);
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function contacts()
    {
        return $this->morphMany(Contact::class , 'contactable');
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function cards()
    {
        return $this->morphMany(Card::class , 'cardable');
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function abouts()
    {
        return $this->morphMany(About::class , 'aboutable');
    }
}
