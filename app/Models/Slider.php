<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Slider extends Model
{
    use SoftDeletes;
    protected $dates = ['deleted_at'];
    protected $fillable=['img','title','link','type','category_id'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

}
