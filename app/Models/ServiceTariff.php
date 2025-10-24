<?php

namespace App\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceTariff extends Model
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
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function centers()
    {
        return $this->belongsToMany(Center::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function tariff()
    {
        return $this->belongsTo(Tariff::class);
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function serviceIndexTariff()
    {
        return $this->belongsTo(ServiceIndexTariff::class);
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function factor_items()
    {
        return $this->belongsToMany(FactorItem::class)->withTimestamps()->whereNull('factor_item_service_tariff.deleted_at')->withPivot('value' , 'id' ,'description','session_number' , 'treatment_duration' , 'user_id' , 'status' , 'price' , 'discount' , 'grouping');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function allocationEmployeeShareCenters()
    {
        return $this->hasMany(AllocationEmployeeShareCenter::class);
    }
    /**
     * @param $query
     * @return mixed
     */
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
        if (isset($tariff_id) && trim($tariff_id) != '')
        {
            $query->whereHas('tariff', function ($query) use ($tariff_id) {
                $query->where('id', $tariff_id);
            });
        }
        else
        {
            $last_tariff_id = request('last_tariff_id');
            $query->whereHas('tariff', function ($query) use ($last_tariff_id) {
                $query->where('id', $last_tariff_id);
            });
        }

        return $query;
    }

    /**
     * @param $query
     * @return mixed
     */
    public function scopeStatus($query)
    {
        return $query->whereStatus(false);
    }
}
