<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tariff extends Model
{
    protected $guarded = [];

    public function serviceTariffs()
    {
        return $this->hasMany(ServiceTariff::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
