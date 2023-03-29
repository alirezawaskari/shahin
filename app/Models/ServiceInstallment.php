<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceInstallment extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    public $timestamps = false;

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
