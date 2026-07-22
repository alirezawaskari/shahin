<?php

namespace App\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Organization extends Model implements HasMedia
{
    use SoftDeletes , Sluggable, InteractsWithMedia, \App\Models\Concerns\AdoptsLegacyMedia;

    /** Touch-to-migrate: legacy single-image columns -> Spatie single-file collections. */
    /** Touch-to-migrate: legacy paths inside the `document` JSON array -> `document` collection. */
    protected function legacyArrayMediaMap(): array
    {
        return ['document' => 'document'];
    }

    protected function legacyMediaMap(): array
    {
        return ['stamp' => 'stamp', 'image' => 'image'];
    }

    /**
     * @var array
     */
    protected $guarded = [];

    /**
     * Spatie media collections. Legacy path columns (stamp, image) are kept, but
     * reads route through the accessors below. `document` is a multi-file
     * collection whose urls are still persisted into the JSON `document` column.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('stamp')->useDisk('public')->singleFile();
        $this->addMediaCollection('image')->useDisk('public')->singleFile();
        $this->addMediaCollection('document')->useDisk('public');
    }

    /** Stamp url (shadows the legacy `stamp` column; disk-checked, legacy fallback). */
    public function getStampAttribute(): string
    {
        return \App\Helpers\MediaLibraryHelper::urlIfFileExists($this, 'stamp')
            ?: ($this->getRawOriginal('stamp') ? asset($this->getRawOriginal('stamp')) : '');
    }

    /** Main image url (shadows the legacy `image` column; disk-checked, legacy fallback). */
    public function getImageAttribute(): string
    {
        return \App\Helpers\MediaLibraryHelper::urlIfFileExists($this, 'image')
            ?: ($this->getRawOriginal('image') ? asset($this->getRawOriginal('image')) : '');
    }

    /**
     * Document urls for display: Spatie media first, then any legacy `document`
     * paths not yet adopted. Not an accessor — the trait reads the raw `document`
     * attribute internally, so shadowing it would break adoption/detection.
     */
    public function documentUrls(): array
    {
        return $this->arrayMediaUrls('document', 'document');
    }

    /**
     * @var string[]
     */
    protected $casts = [
        'organ_code' => 'array',
		'sms_code' => 'array',
        'document' => 'array'
    ];

    /**
     *types of organization
     */
    const TYPE = [
        'referred' => 'مراجعه کننده',
        'contracting_party' => 'طرف قرارداد',
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
                'source' => 'fa_name'
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
        return "/organizations/$this->slug";
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function services()
    {
        return $this->belongsToMany(Service::class);
    }


    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function factors()
    {
        return $this->hasMany(Factor::class);
    }


    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function users()
    {
        return $this->hasMany(User::class ,'organization_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function admins()
    {
        return $this->hasMany(User::class ,'admin_organization_id');
    }

    /**
     * @param $query
     * @return mixed
     */
    public function scopeFilter($query)
    {
        $name = request('name');
        $type = request('type');

        if (isset($name) && trim($name) != '') {
            $query->where('fa_name', 'LIKE', '%' . $name . '%')->orWhereHas('children',function ($query)use ($name){
                $query->where('fa_name', 'LIKE', '%' . $name . '%');
            });
        }

        if (isset($type) && $type != 'all') {
            $query->where('type' , $type);
        }

        return $query;
    }

    /**
     * @param $status
     * @return string
     */
    public static function status($status)
    {
        return [
            '0' => 'راکد',
            '1' => 'نیمه جاری',
            '2' => 'جاری',
        ][$status];
    }

    /**
     * @param $status
     * @return string
     */
    public static function getClassByStatus($status)
    {
        return [
            '0' => 'dark',
            '1' => 'warning',
            '2' => 'danger',
        ][$status];
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function children()
    {
        return $this->hasMany(self::class, 'parent_id');
    }
}
