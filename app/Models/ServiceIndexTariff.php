<?php

namespace App\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceIndexTariff extends Model
{
    use Sluggable,SoftDeletes;

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
                'source' => 'name'
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
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function service()
    {
        return $this->belongsTo(Service::class);
    }
     /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function agent()
    {
        return $this->belongsTo(User::class , 'agent_id');
    }
    /**
     * @param $query
     * @return mixed
     */
    public function scopeFilter($query)
    {
        $title = request('title');

        if (isset($title) && trim($title) != '') {
            $query->where('title','like', '%'.$title.'%');
        }
        return $query;
    }
}
