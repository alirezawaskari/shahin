<?php

namespace App\Models;

use App\Helpers\FilterHelper;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
class Service extends Model implements AuditableContract, HasMedia
{
    use Auditable;
    use Sluggable,SoftDeletes,InteractsWithMedia,\App\Models\Concerns\AdoptsLegacyMedia;

    /** Touch-to-migrate: legacy single-image columns -> Spatie single-file collections. */
    protected function legacyMediaMap(): array
    {
        return [
            'image'     => 'image',
            'icon'      => 'icon',
            'img_box_1' => 'img_box_1',
            'thumb'     => 'thumb',
            'image_app' => 'image_app',
        ];
    }

    /**
     * Touch-to-migrate: legacy `galleries` rows -> the homepage-banner slot collections.
     *
     * The type mapping is taken from the ORIGINAL writer, ServiceController::uploadSiteImages()
     * before the Spatie migration (commit 08c1026c):
     *     verticalImages   -> Gallery type 0
     *     horizontalImages -> Gallery type 1
     * Note this is NOT the mapping Category/GhorfeOnlineList use (1=desktop, 0=mobile): the
     * legacy `type` column means different things per model, so it must never be assumed.
     *
     * Legacy type 2 rows also exist for services (20, under /upload/gallery/, all titled
     * "تست 1"), but nothing in the codebase — past or present — ever wrote a Gallery with
     * type 2, so there is no collection they belong to. They are deliberately NOT adopted.
     */
    protected function legacyGalleryMigrations(): array
    {
        return [
            ['collection' => 'gallery_horizontal', 'type' => 1, 'slotted' => true,
             'max' => self::GALLERY_COLLECTIONS['gallery_horizontal']],
            ['collection' => 'gallery_vertical',   'type' => 0, 'slotted' => true,
             'max' => self::GALLERY_COLLECTIONS['gallery_vertical']],
        ];
    }

    /** Media collections owned directly by the service (Spatie native). */
    const MEDIA_FIELDS = ['image', 'icon', 'img_box_1', 'thumb', 'image_app'];

    /** Multi-image gallery collections (data-mining / homepage slider). */
    const GALLERY_COLLECTIONS = [
        'gallery_horizontal' => 4, // max horizontal banners
        'gallery_vertical'   => 2, // max vertical banners
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

    public function getImgBox1Attribute(): string
    {
        return \App\Helpers\MediaLibraryHelper::urlIfFileExists($this, 'img_box_1')
            ?: ($this->getRawOriginal('img_box_1') ? asset($this->getRawOriginal('img_box_1')) : '');
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

    /**
     * @var string[]
     */
    protected $fillable=['title','category_id',
        'en_title','slug','description','content',
        'status', 'isShow','price','image','pay_type',
        'purchase_type','min_credit','max_credit',
        'ability_invite_friend', 'items',
        'center','multiple','percent',
        'demo','content','txt_box_1','left_content'
        ,'img_box_1','thumb','image','icon','image_app'
        ,'background_image','gift','wage_percent','session_attributes','tax','rules','session'];

    /**
     * @var array
     */
    protected $guarded = [];
    /**
     * @var array
     */
    protected $casts = [
        'after_fields' => 'array',
        'required' => 'array',
        'gift' => 'array',
        'session_attributes' => 'array',
    ];

    /**
     *purchaseTypes
     */
    const PURCHASETYPE = [
        '0' => 'آنلاین',
        '1' => 'حضوری',
        '2' => 'هر دو',
    ];

    /**
     *payTypes
     */
    const PAYTYPE = [
        '0' => 'نقد',
        '1' => 'اقساط',
        '2' => 'هر دو',
    ];
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
        return "/services/$this->slug";
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function financialPlansTypes()
    {
        return $this->morphToMany(FinancialPlansType::class , 'financialplanstypeItemable' , 'financial_plans_type_items');
    }

    /**
     * Get all of the check's files.
     */
    public function files()
    {
        return $this->morphMany(File::class, 'fileable');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function financialPlansTypeItems()
    {
        return $this->morphMany(FinancialPlansTypeItem::class , 'financialplanstypeItemable');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function tariffs()
    {
        return $this->hasMany(Tariff::class)->orderBy('id' , 'desc');
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function serviceTariffs()
    {
        return $this->hasMany(ServiceTariff::class)->orderBy('id' , 'desc');
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function serviceIndexTariffs()
    {
        return $this->hasMany(ServiceIndexTariff::class)->orderBy('id' , 'desc');
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function keywords()
    {
        return $this->belongsToMany(Keyword::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function groupsms()
    {
        return $this->belongsToMany(Groupsms::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function coupons()
    {
        return $this->belongsToMany(Coupon::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function representations()
    {
        return $this->belongsToMany(Representation::class);
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function singleWebPages()
    {
        return $this->belongsToMany(SingleWebPage::class);
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function service_items()
    {
        return $this->hasMany(ServiceItem::class);
    }
    /**
     * Summary of activeServiceItems
     * @param Service $item
     * @return \Illuminate\Database\Eloquent\Collection<int, TRelatedModel>
     */
    public function activeServiceItems(Service $item)
    {
        return $item->service_items()->where('status' , true)->where('isShow' , true)->get();
    }
     /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function ghorfeOnlineLists()
    {
        return $this->belongsToMany(GhorfeOnlineList::class);
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function installments()
    {
        return $this->morphMany(Installment::class , 'installmentable');
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function percentageAllocationEmployees()
    {
        return $this->morphMany(PercentageAllocationEmployee::class , 'percentage_allocation_employeeable');
    }

    /**
     * @param $query
     * @return mixed
     */
    public function scopeStatus($query)
    {
        return $query->whereStatus(false);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function factoritems()
    {
        return $this->morphMany(FactorItem::class, 'factoritemable');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function beforeforms()
    {
        return $this->morphMany(BeforeForm::class, 'beforeformable');
    }

//    /**
//     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
//     */
//    public function centers()
//    {
//        return $this->morphMany(Center::class, 'centerable');
//    }

    /**
     * Get all of the centers for the post.
     */
    public function centers()
    {
        return $this->morphToMany(Center::class, 'centerable');
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function galleries()
    {
        return $this->morphMany(Gallery::class , 'galleryable');
    }

    //service By service many to many

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function similarGroups()
    {
        return $this->belongsToMany(Service::class,'similar_groups','similarable_id','relation_id')
            ->withPivotValue(['similar_model' =>Service::class, 'relation_model' => Service::class]);
    }

    //service By category many to many

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function similarGroupCategories()
    {
        return $this->belongsToMany(Category::class,'similar_groups','similarable_id','relation_id')
            ->withPivotValue(['similar_model' => Service::class, 'relation_model' => Category::class]);
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
    public function askedQuestions()
    {
        return $this->morphMany(AskedQuestion::class, 'askedquestionable');
    }

    /**
     * @param $id
     * @param $field
     * @return string
     */
    public static function serviceById($id, $field)
    {
        if($id){
            $service = Service::find($id);
            if($service){return $service->$field;}
        }
        return '';
    }

    //get status

    /**
     * @param $status
     * @return string
     */
    public static function status($status)
    {
        if($status){
           return 'فعال';
        }
        else{
            return 'غیر فعال';
        }
    }

    //get is show site status

    /**
     * @param $is_show
     * @return string
     */
    public static function isShow($is_show)
    {
        if($is_show){
           return 'فعال';
        }
        else{
            return 'غیر فعال';
        }
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function menu()
    {
        return $this->morphOne(Menu::class, 'menuable');
    }
        /**
     * @param $query
     * @return mixed
     */
    public function scopeFilter($query)
    {
        $title = request('title');
        $purchase_type = request('purchase_type');
        $pay_type = request('pay_type');
        $query = FilterHelper::getDataByCheckGhorfeExistOrNot($query , 'belongsToMany' , null);

        if (auth()->user() && auth()->user()->admin_representation) {
            $representation = auth()->user()->admin_representation->id;
            $query->whereHas('representations', function ($query) use ($representation) {
                $query->where('representation_id', $representation);
            });
        }
        if (auth()->user() && auth()->user()->center_id) {
            $center = auth()->user()->center_id;
            $query->whereHas('centers', function ($query) use ($center) {
                $query->where('center_id', $center);
            });
        }

        if (isset($title) && trim($title) != '') {
            $query->where('title', 'LIKE', '%' . $title . '%');
        }

        if (isset($purchase_type) && $purchase_type != 'all') {
            $query->where('purchase_type',$purchase_type);
        }

        if (isset($pay_type) && $pay_type != 'all') {
            $query->where('pay_type',$pay_type);
        }
        return $query;
    }
    /**
     * @param Service $item
     * @return mixed
     */
    public static function setRepresentation(Service $item)
    {
        $representation = $item->representations->first();
        if($item->isShow)
        {
            $representation = null;
        }

        return $representation ? $representation->id : null;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
