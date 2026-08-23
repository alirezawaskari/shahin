<?php

namespace App\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class GhorfeOnlineList extends Model implements HasMedia
{
    use SoftDeletes , Sluggable, InteractsWithMedia, \App\Models\Concerns\AdoptsLegacyMedia;

    /**
     * Touch-to-migrate: legacy single-image columns -> Spatie single-file collections.
     *
     * Only footer_logo is here: it is the one field with a real legacy PATH column. The
     * logo / fav / mohr / addvertisment_slider legacy values live in the *_id columns
     * (logo_id, fav_id, ...) as MediaLibrary UUID FKs, not paths — a different shape this
     * path-based map cannot adopt, so mapping them here would be dead code.
     */
    protected function legacyMediaMap(): array
    {
        return [
            'footer_logo' => 'footer_logo',
        ];
    }

    /**
     * Touch-to-migrate: the legacy *_id MediaLibrary UUID FKs -> single-file collections.
     * These reference a media row on the PRIVATE disk rather than a path under public/,
     * which is why they need their own map (see AdoptsLegacyMedia::legacyMediaLibraryMap).
     * Without it these images were unreachable: the accessors fell back to
     * getRawOriginal('logo'), but no such column exists — only logo_id.
     */
    protected function legacyMediaLibraryMap(): array
    {
        return [
            'logo'                 => 'logo_id',
            'footer_logo'          => 'footer_logo_id',
            'fav'                  => 'fav_id',
            'mohr'                 => 'mohr_id',
            'addvertisment_slider' => 'addvertisment_slider_id',
        ];
    }

    /** Touch-to-migrate: legacy gallery rows -> slot collections (desktop=type 1, mobile=type 0). */
    protected function legacyGalleryMigrations(): array
    {
        return [
            ['collection' => 'gallery_desktop', 'type' => 1, 'slotted' => true, 'max' => 5],
            ['collection' => 'gallery_mobile',  'type' => 0, 'slotted' => true, 'max' => 5],
        ];
    }

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

    /**
     * The businesses that are switched on — `status`, the same فعال/غیرفعال the businesses
     * list renders and the edit form writes.
     *
     * Every admin screen that OFFERS a business to pick goes through here: a business whose
     * contract has been switched off must not be selectable any more, even though its rows
     * stay in place and stay reachable for whoever is pinned to it.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('status', 1);
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

    /**
     * Settings-image url accessors: Spatie first, then the legacy path column (only
     * footer_logo has one), then the legacy *_id MediaLibrary reference.
     *
     * That last fallback is what makes the pre-Spatie images visible at all — logo, fav,
     * mohr and addvertisment_slider have NO path column, so their old getRawOriginal()
     * fallback could never return anything and the images silently vanished from the admin.
     */
    public function getLogoAttribute(): string
    {
        return \App\Helpers\MediaLibraryHelper::urlIfFileExists($this, 'logo')
            ?: $this->legacyMediaLibraryUrl('logo');
    }

    public function getFooterLogoAttribute(): string
    {
        return \App\Helpers\MediaLibraryHelper::urlIfFileExists($this, 'footer_logo')
            ?: ($this->getRawOriginal('footer_logo') ? asset($this->getRawOriginal('footer_logo')) : '')
            ?: $this->legacyMediaLibraryUrl('footer_logo');
    }

    public function getFavAttribute(): string
    {
        return \App\Helpers\MediaLibraryHelper::urlIfFileExists($this, 'fav')
            ?: $this->legacyMediaLibraryUrl('fav');
    }

    public function getMohrAttribute(): string
    {
        return \App\Helpers\MediaLibraryHelper::urlIfFileExists($this, 'mohr')
            ?: $this->legacyMediaLibraryUrl('mohr');
    }

    public function getAddvertismentSliderAttribute(): string
    {
        return \App\Helpers\MediaLibraryHelper::urlIfFileExists($this, 'addvertisment_slider')
            ?: $this->legacyMediaLibraryUrl('addvertisment_slider');
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
    public function askedQuestions()
    {
        return $this->morphMany(AskedQuestion::class, 'askedquestionable');
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
     * Branches (شعبه) of this business — the list the about-us page renders and the branch
     * frontend links into. Ordered by the pivot `sort` so the admin controls the cards'
     * order; rows with no sort fall to the end (MySQL puts NULL first ascending).
     *
     * `sort` is only meaningful on THESE rows: `center_owners` is shared with
     * Representation::centers(), whose rows leave it null and are never ordered by it.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function centers()
    {
        return $this->morphToMany(Center::class, 'owner', 'center_owners')
            ->withPivot('sort')
            ->orderByRaw('center_owners.sort is null, center_owners.sort');
    }
    /**
     * Staff (همکاران متخصص) of this business — the people its about-us page presents.
     *
     * `employees.ghorfe_online_list_id`, a column on the employees table and nothing else.
     * Not the branch (a branch is a place, and someone can work for the business without
     * being posted to one) and not `ghorfe_online_list_user` (that pivot is about the user
     * account, and reading it meant an employee whose user row was missing or soft-deleted
     * dropped off the list — which a save then read as a removal).
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function staff()
    {
        return $this->hasMany(Employee::class);
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function faqs()
    {
        return $this->hasMany(Faq::class);
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
