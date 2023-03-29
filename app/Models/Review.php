<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Review extends Model
{
    use SoftDeletes;
      protected $dates = ['deleted_at'];

      protected $fillable=['product_id','type','text','reviewable_id','reviewable_type'];

    public function reviewable()
    {
        return $this->morphTo();
    }

}
