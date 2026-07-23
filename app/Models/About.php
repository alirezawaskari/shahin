<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class About extends Model implements HasMedia {
    use SoftDeletes, InteractsWithMedia, \App\Models\Concerns\AdoptsLegacyMedia;

    /**
     * Touch-to-migrate: the abandoned image_*_id -> MediaLibrary UUID FKs.
     *
     * There is nothing PATH-shaped to adopt here (image_two..image_five are collections,
     * not columns), which is why this model had no legacy map at all — until
     * legacyMediaLibraryMap() added support for the FK shape.
     *
     * The numbering is off by one and that is not a typo: the boxes are labelled 2..5 in
     * the UI, but the FK columns number the same four images 1..4. The original
     * create_abouts_table migration had exactly four box columns — image_tow (sic),
     * image_three, image_four, image_five — which these four FKs replaced in order.
     */
    protected function legacyMediaLibraryMap(): array
    {
        return [
            'image_two'   => 'image_one_id',
            'image_three' => 'image_two_id',
            'image_four'  => 'image_three_id',
            'image_five'  => 'image_four_id',
        ];
    }

    protected $dates = [ 'deleted_at' ];

    // image_two..image_five are Spatie media collections (accessors below), NOT columns,
    // so they must stay out of $fillable — mass-assigning them threw "Unknown column".
    protected $fillable = [ 'ghorfe_online_list_id', 'title', 'content', 'content2',
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

    /**
     * Box-image urls: Spatie first, then the legacy image_*_id MediaLibrary reference.
     *
     * The old getRawOriginal('image_two') fallback was dead code — image_two..image_five
     * are collections, not columns (the original text columns were dropped), so it could
     * never return anything and pre-Spatie box images were invisible in the admin.
     */
    public function getImageTwoAttribute(): string
    {
        return \App\Helpers\MediaLibraryHelper::urlIfFileExists($this, 'image_two')
            ?: $this->legacyMediaLibraryUrl('image_two');
    }

    public function getImageThreeAttribute(): string
    {
        return \App\Helpers\MediaLibraryHelper::urlIfFileExists($this, 'image_three')
            ?: $this->legacyMediaLibraryUrl('image_three');
    }

    public function getImageFourAttribute(): string
    {
        return \App\Helpers\MediaLibraryHelper::urlIfFileExists($this, 'image_four')
            ?: $this->legacyMediaLibraryUrl('image_four');
    }

    public function getImageFiveAttribute(): string
    {
        return \App\Helpers\MediaLibraryHelper::urlIfFileExists($this, 'image_five')
            ?: $this->legacyMediaLibraryUrl('image_five');
    }

    /**
    * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
    */
    public function ghorfeOnline() {
        return $this->belongsTo( GhorfeOnlineList::class, 'ghorfe_online_list_id' );
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
