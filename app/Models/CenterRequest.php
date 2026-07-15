<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class CenterRequest extends Model implements HasMedia {
    use SoftDeletes, InteractsWithMedia, \App\Models\Concerns\AdoptsLegacyMedia;

    /**
     * Spatie media collection for gallery images attached to this request. Multi-file.
     * This is now the ONLY place new gallery images are written: the dual-write that also
     * mirrored each media's URL into a legacy `galleries` row has been removed, so nothing
     * in the app creates legacy gallery rows any more.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('galleries')->useDisk('public');
    }

    /**
     * Touch-to-migrate: legacy `galleries` rows -> the Spatie 'galleries' collection.
     *
     * Every CenterRequest gallery row is type 0 (the old writer never passed a type), so
     * there is no type filter and no cap. Titles carry across as the media's `title` custom
     * property — the same key attachTo() now stores for new uploads, so both systems read
     * back identically.
     *
     * Rows left behind by the retired dual-write hold a /storage/ URL rather than a legacy
     * /upload/ path. They only exist where Spatie media already exists, and adoptLegacyMedia()
     * skips a collection that still has media, so they are never re-adopted as duplicates.
     */
    protected function legacyGalleryMigrations(): array
    {
        return [
            ['collection' => 'galleries', 'type' => null, 'slotted' => false, 'max' => null],
        ];
    }

    /**
     * Gallery images to display: Spatie media if any, else the legacy `galleries` rows (which
     * the next save migrates). Each entry exposes ->url and ->title so callers don't need to
     * know which system served it. Read-only — legacy rows are never modified.
     */
    public function galleryImages(): \Illuminate\Support\Collection
    {
        $media = \App\Helpers\MediaLibraryHelper::existingMedia($this, 'galleries');
        if ($media->isNotEmpty()) {
            return $media->map(fn ($m) => (object) [
                'url'   => $m->getUrl(),
                'title' => $m->getCustomProperty('title'),
            ])->values();
        }

        return $this->galleries
            ->filter(fn ($g) => ! empty($g->image))
            ->map(fn ($g) => (object) ['url' => $g->image, 'title' => $g->title])
            ->values();
    }

    /**
    * @var array
    */
     protected $guarded = [];

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     *
     */
    const TYPE = [
        'seller' => 'فروشنده',
        'producer' => 'تولید کننده',
        'supplier' => 'تامین کننده',
        'service_provider' => 'خدمات دهنده'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function service() {
        return $this->belongsTo( Service::class );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user() {
        return $this->belongsTo( User::class );
    }

     /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function representation() {
        return $this->belongsTo( Representation::class );
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function center()
    {
        return $this->hasOne(Center::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function center_category() {
        return $this->belongsTo( CenterCategory::class, 'center_category_id' );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function province() {
        return $this->belongsTo( Province::class, 'province_id' );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function galleries()
    {
        return $this->morphMany(Gallery::class , 'galleryable');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function histories()
    {
        return $this->morphMany(History::class , 'historiable');
    }

    /**
     * @param $query
     * @return mixed
     */
    public function scopeFilter($query ) {
        $name = request( 'name' );
        $subject = request( 'subject' );
        $phone = request( 'phone' );
        $province = request( 'province' );
        $type = request( 'type' );
        $service_category = request( 'service_category' );
        $product_category = request( 'product_category' );
        $status = request( 'status' );
        $start_date = request( 'start_date' );
        $end_date = request( 'end_date' );
        $representation = request('representation');

        if ( isset( $name ) && trim( $name ) != '' ) {
            $query->where( 'name', 'LIKE', '%' . $name . '%' );
        }
        
        if (isset($representation) && trim($representation) != 'all') {
            $query->where('representation_id',$representation);
        }

        if ( isset( $subject ) && trim( $subject ) != '' ) {
            $query->where( 'subject', 'LIKE', '%' . $subject . '%' );
        }

        if ( isset( $phone ) && trim( $phone ) != '' ) {
            $query->where( 'phone', $phone );
        }

        if ( isset( $province ) && trim( $province ) != 'all' ) {
            $query->where( 'province_id', $province );
        }

        if ( isset( $status ) && trim( $status ) != 'all' ) {
            $query->where( 'status', $status );
        }

        if ( isset( $type ) && trim( $type ) != 'all' ) {
            $query->where( 'type', $type );
        }

        if ( isset( $product_category ) && trim( $product_category ) != 'all' ) {
            $query->where( 'center_category_id', $product_category );
        }

        if ( isset( $service_category ) && trim( $service_category ) != 'all' ) {
            $query->where( 'center_category_id', $service_category );
        }

        if ( isset( $start_date ) && trim( $start_date ) != '' && isset( $end_date ) && trim( $end_date ) != '' ) {
            $query->whereDate( 'created_at', '>=', $start_date )->whereDate( 'created_at', '<=', $end_date );
        }

        return $query;
    }

    /**
     * @param $status
     * @return string
     */
    public static function status($status ) {
        switch( $status ) {
            case 0:
            return 'جدید';
            break;
            case 1:
            return 'فعال';
            break;
            case 2:
            return 'معلق';
            break;
            case 3:
            return 'رد شده';
            break;
            default:
            return 'جدید';
        }
    }
}
