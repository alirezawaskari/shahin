<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tariff extends Model
{
    /**
     * @var array
     */
    protected $guarded = [];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function scopeFilter($query)
    {
        $title = request('title');
        $user = request('user');
        if(isset($user) && $user != 'all')
        {
            $query->where('user_id', $user);
        }
        if(isset($title) && $title != '')
        {
            $query->where('title', 'LIKE', '%' . $title . '%');
        }
        return $query;
    }
    public function serviceTariffs()
    {
        return $this->hasMany(ServiceTariff::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
