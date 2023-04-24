<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Gallery extends Model
{
    /**
     * @var array
     */
    protected $guarded = [];

    use SoftDeletes;
    protected $fillable=['galleryable_id','galleryable_type','title','image','status','type'];

    protected $dates = ['deleted_at'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function gallerycategory()
    {
        return $this->belongsTo(GalleryCategory::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function galleryable()
    {
        return $this->morphTo();
    }
}
