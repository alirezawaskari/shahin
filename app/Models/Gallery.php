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
    protected $fillable=['galleryable_id','galleryable_type','title','image','status','type','parent_id','position_index'];

    protected $dates = ['deleted_at'];

    /*
     * gallerycategory() / galleryghorfe() removed with the GalleryCategory model: both were
     * belongsTo(GalleryCategory::class), but `gallery_categories` does not exist as a table
     * and `galleries` has no gallery_category_id column, so neither relation could ever
     * resolve. Nothing called them.
     */

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function galleryable()
    {
        return $this->morphTo();
    }
}
