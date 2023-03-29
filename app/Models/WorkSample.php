<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkSample extends Model
{
    use SoftDeletes;
    protected $fillable=['worksampleable_id','worksampleable_type','title','image','status'];

    protected $dates = ['deleted_at'];

    public function worksampleable()
    {
        return $this->morphTo();
    }
}
