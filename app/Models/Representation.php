<?php

namespace App\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Representation extends Model
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
        'sms_code' => 'array'
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
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
    */
    public function centers()
    {
        return $this->belongsToMany(Center::class);
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function sliders()
    {
        return $this->hasMany(Slider::class);
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
