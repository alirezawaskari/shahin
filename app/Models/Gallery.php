<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Gallery extends Model
{
    protected $guarded = [];

    use SoftDeletes;
    protected $fillable=['galleryable_id','galleryable_type','title','image','status','type'];

    protected $dates = ['deleted_at'];

    public function gallerycategory()
    {
        return $this->belongsTo(GalleryCategory::class);
    }

    public function galleryable()
    {
        return $this->morphTo();
    }
}
