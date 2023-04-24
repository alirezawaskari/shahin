<?php

namespace App\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Organization extends Model
{
    use SoftDeletes , Sluggable;
    /**
     * @var array
     */
    protected $guarded = [];


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
