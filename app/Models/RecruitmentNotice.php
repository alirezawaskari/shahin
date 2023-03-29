<?php

namespace App\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RecruitmentNotice extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    public function province()
    {
        return $this->belongsTo(Province::class);
    }

}
