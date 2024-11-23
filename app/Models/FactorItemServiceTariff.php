<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FactorItemServiceTariff extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected $dates = ['deleted_at'];

    protected $table = 'factor_item_service_tariff';

    public function factorItem()
    {
        return $this->belongsTo(FactorItem::class);
    }

    public function serviceTariff()
    {
        return $this->belongsTo(ServiceTariff::class);
    }

    public function agent()
    {
        return $this->belongsTo(User::class);
    }

    public function sessions()
    {
        return $this->hasMany(Session::class)->orderBy('date');
    }
}
