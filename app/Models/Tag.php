<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Cviebrock\EloquentSluggable\Sluggable;

class Tag extends Model
{
    use Sluggable,SoftDeletes;
    protected $table='tagging_tags';
    protected $dates = ['deleted_at'];

    public $timestamps=false;
    protected $fillable=['tag_group_id','slug','name'];
    public function sluggable(): array
    {
        return ['slug' => ['source' => 'name']];
    }
}
