<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * Owner of every file uploaded through a CKEditor instance. Each upload creates one
 * Ckeditor row holding the file in its Spatie 'file' collection. Because the media
 * path generator keys the folder off the model name, files land under
 * storage/app/public/ckeditor/... (public disk + the storage symlink), so getUrl()
 * is a directly-servable public URL that CKEditor embeds into the content HTML.
 */
class Ckeditor extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $guarded = [];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('file')->useDisk('public')->singleFile();
    }
}
