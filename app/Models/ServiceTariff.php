<?php

namespace App\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceTariff extends Model
{
    use Sluggable,SoftDeletes;

    protected $guarded = [];

    public function sluggable():array
    {
        return [
            'slug' => [
                'source' => 'name'
            ]
        ];
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function centers()
    {
        return $this->belongsToMany(Center::class);
    }

    public function tariff()
    {
        return $this->belongsTo(Tariff::class);
    }

    public function factor_items()
    {
        return $this->belongsToMany(FactorItem::class)->withTimestamps()->whereNull('factor_item_service_tariff.deleted_at')->withPivot('value' , 'id' ,'session_number' , 'treatment_duration' , 'user_id' , 'status' , 'price' , 'discount' , 'grouping');
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function scopeFilter($query)
    {
        $tariff_id = request('tariff_id');
        $name = request('title');
        $center_id = request('center_id');

        if (isset($name) && trim($name) != '') {
            $query->where('name','like', '%'.$name.'%');
        }

        if (isset($center_id) && trim($center_id) != '') {

            $query->whereHas('centers', function ($query) use ($center_id) {
                $query->where('center_id', $center_id);
            });

        }
        if (isset($tariff_id) && trim($tariff_id) != '') {

            $query->whereHas('tariff', function ($query) use ($tariff_id) {
                $query->where('id', $tariff_id);
            });
        }

        return $query;
    }

    public function scopeStatus($query)
    {
        return $query->whereStatus(false);
    }
}
