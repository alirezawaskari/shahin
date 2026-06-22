<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasVersion4Uuids;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class MediaLibrary extends Model implements HasMedia
{
    use HasVersion4Uuids;
    use InteractsWithMedia;

    protected $fillable = [
        "title",
        "alt",
        "width",
        "height",
        "user_id",
        "upload_location",
    ];

}
