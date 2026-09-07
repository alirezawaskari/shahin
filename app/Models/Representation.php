<?php

namespace App\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Representation extends Model implements HasMedia
{
    use SoftDeletes , Sluggable, InteractsWithMedia, \App\Models\Concerns\AdoptsLegacyMedia;

    /** Touch-to-migrate: legacy single-image columns -> Spatie single-file collections. */
    protected function legacyMediaMap(): array
    {
        return ['image' => 'image', 'stamp' => 'stamp'];
    }

    /** Document images owned by the representation (logo/header image, stamp). */
    const MEDIA_FIELDS = ['image', 'stamp'];

    /**
     * @var array
     */
    protected $guarded = [];


    /**
     * @var string[]
     */
    protected $casts = [
        'sms_code' => 'array'
    ];

    public function registerMediaCollections(): void
    {
        foreach (self::MEDIA_FIELDS as $field) {
            $this->addMediaCollection($field)->useDisk('public')->singleFile();
        }
    }

    public function getImageAttribute(): string
    {
        return \App\Helpers\MediaLibraryHelper::urlIfFileExists($this, 'image')
            ?: ($this->getRawOriginal('image') ? asset($this->getRawOriginal('image')) : '');
    }

    public function getStampAttribute(): string
    {
        return \App\Helpers\MediaLibraryHelper::urlIfFileExists($this, 'stamp')
            ?: ($this->getRawOriginal('stamp') ? asset($this->getRawOriginal('stamp')) : '');
    }
    /**
     * Return the sluggable configuration array for this model.
     *
     * @return array
     */
    public function sluggable():array
    {
        return [
            'slug' => [
                'source' => 'en_name'
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
        return "/$this->slug";
    }

    public function settings()
    {
        return $this->morphMany(Setting::class, 'settingable');
    }

    /*
    |-----------------------------------
    | HELPER ACCESS (خیلی مهم 😎)
    |-----------------------------------
    */
    public function getSetting($key, $default = null)
    {
        $settings = $this->settings()
            ->pluck('value', 'key');

        return $settings[$key] ?? $default;
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
    public function articles()
    {
        return $this->belongsToMany(Article::class);
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
    */
    public function financialPlansTypes()
    {
        return $this->belongsToMany(FinancialPlansType::class);
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
    */
    public function centers()
    {
        return $this->morphToMany(Center::class, 'owner', 'center_owners');
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function sliders()
    {
        return $this->hasMany(Slider::class);
    }
    /**
     * "درباره ما" rows owned by this representation (mirrors GhorfeOnlineList::about()).
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function abouts()
    {
        return $this->morphMany(About::class, 'aboutable');
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function centerRequests()
    {
        return $this->hasMany(CenterRequest::class);
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function recruitmentNotices()
    {
        return $this->hasMany(RecruitmentNotice::class);
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function resumes()
    {
        return $this->hasMany(Resume::class);
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
    public function callcenters()
    {
        return $this->hasMany(Callcenter::class);
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function tickets()
    {
        return $this->hasMany(Ticket::class , 'ticketable_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function users()
    {
        return $this->hasMany(User::class ,'representation_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function admins()
    {
        return $this->hasMany(User::class ,'admin_representation_id');
    }
}
