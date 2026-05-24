<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Cviebrock\EloquentSluggable\Sluggable;

class Theme extends Model
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];


    protected $fillable=['themplate_name','color_primary','color-secondary','type','name'];

}
