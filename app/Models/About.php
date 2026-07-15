<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class About extends Model implements HasMedia {
    use SoftDeletes, InteractsWithMedia;
    protected $dates = [ 'deleted_at' ];
    protected $fillable = [ 'ghorfe_online_list', 'image_two', 'image_three', 'image_four', 'image_five', 'content', 'content2',
    'content_one', 'content_two', 'content_three', 'content_four', 'content_five' ];

    /**
     * Spatie media: one single-file collection per "box" image (2..5). Replaces the
     * abandoned image_*_id -> MediaLibrary FK / HasMediaImage-trait approach (which was
     * incompatible with the uploadFilesTemporarily + attachTo pipeline the app uses).
     * The legacy image_*_id columns are left in place (unused, no drops).
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('image_two')->useDisk('public')->singleFile();
        $this->addMediaCollection('image_three')->useDisk('public')->singleFile();
        $this->addMediaCollection('image_four')->useDisk('public')->singleFile();
        $this->addMediaCollection('image_five')->useDisk('public')->singleFile();
    }

    /** Box-image urls (shadow the legacy image_* names; disk-checked). */
    public function getImageTwoAttribute(): string
    {
        return \App\Helpers\MediaLibraryHelper::urlIfFileExists($this, 'image_two');
    }

    public function getImageThreeAttribute(): string
    {
        return \App\Helpers\MediaLibraryHelper::urlIfFileExists($this, 'image_three');
    }

    public function getImageFourAttribute(): string
    {
        return \App\Helpers\MediaLibraryHelper::urlIfFileExists($this, 'image_four');
    }

    public function getImageFiveAttribute(): string
    {
        return \App\Helpers\MediaLibraryHelper::urlIfFileExists($this, 'image_five');
    }

    /**
    * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
    */

    public function ghorfeOnline() {
        return $this->belongsTo( GhorfeOnlineList::class, 'ghorfe_online_list_id' );
    }

    public function image_one(): BelongsTo {
        return $this->belongsTo( MediaLibrary::class );
    }

    public function getImageOneUrlAttribute() {
        return optional( $this->image_one )->getFirstMediaUrl( 'images' );
    }

    public function image_two(): BelongsTo {
        return $this->belongsTo( MediaLibrary::class );
    }

    public function getImageTwoUrlAttribute() {
        return optional( $this->image_two )->getFirstMediaUrl( 'images' );
    }

    public function image_three(): BelongsTo {
        return $this->belongsTo( MediaLibrary::class );
    }

    public function getImageThreeUrlAttribute() {
        return optional( $this->image_three )->getFirstMediaUrl( 'images' );
    }

    public function image_four(): BelongsTo {
        return $this->belongsTo( MediaLibrary::class );
    }

    public function getImageFourUrlAttribute() {
        return optional( $this->image_four )->getFirstMediaUrl( 'images' );
    }
    /**
    * @param $query
    * @return mixed
    */

    public function scopeFilter( $query ) {
        $query->where( 'ghorfe_online_list_id', null );

        $query->whereHas('sliderable',function ($query) use ($ghorfeOnlineList){
            $query->Where('sliderable_id', $ghorfeOnlineList->id)->Where('sliderable_type', 'App\Models\GhorfeOnlineList');
        });
        
        return $query;
    }
}
