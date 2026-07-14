<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Concerns\HasVersion4Uuids;

class MediaLibrary extends Model implements HasMedia {
    use HasVersion4Uuids;
    use InteractsWithMedia;

    protected $fillable = [
        'title',
        'alt',
        'width',
        'height',
        'user_id',
        'upload_location',
    ];

    public function registerMediaCollections(): void {
        // Private staging disk for temporary ajax uploads. Kept off the public
        // media disk so moving a temp upload into a model's collection is a
        // cross-disk copy (works on both Linux and Windows).
        $this->addMediaCollection( 'images' )
        ->useDisk( 'local' );
    }
}
