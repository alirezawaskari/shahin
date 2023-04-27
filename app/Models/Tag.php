<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Cviebrock\EloquentSluggable\Sluggable;

class Tag extends Model
{
    use Sluggable,SoftDeletes;

    /**
     * @var string
     */
    protected $table='tagging_tags';
    /**
     * @var string[]
     */
    protected $dates = ['deleted_at'];

    /**
     * @var bool
     */
    public $timestamps=false;
    /**
     * @var string[]
     */
    protected $fillable=['tag_group_id','slug','name'];

    /**
     * @return \string[][]
     */
    public function sluggable(): array
    {
        return ['slug' => ['source' => 'name']];
    }
}
