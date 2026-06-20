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
    public function getActiveDomainName() {
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
