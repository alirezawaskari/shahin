<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GalleryCategory extends Model
{
    protected $guarded = [];

    public function galleries()
    {
      return $this->hasMany(Gallery::class);

    }
}
