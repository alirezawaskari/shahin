<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CenterServiceTariff extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected $table = 'center_service_tariff';

    public function center()
    {
        return $this->belongsTo(Center::class);
    }

    public function service_tariff()
    {
        return $this->belongsTo(ServiceTariff::class);
    }
}
