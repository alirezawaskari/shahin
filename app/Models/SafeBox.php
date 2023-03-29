<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SafeBox extends Model
{
    protected $guarded = [];

    public function representation()
    {
        return $this->belongsTo(Representation::class);
    }
}
