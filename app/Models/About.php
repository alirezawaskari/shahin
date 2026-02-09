<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class About extends Model {
    use SoftDeletes;
    protected $dates = [ 'deleted_at' ];
    protected $fillable = [ 'ghorfe_online_list', 'image_two', 'image_three', 'image_four', 'image_five', 'content', 'content2',
    'content_one', 'content_two', 'content_three', 'content_four', 'content_five' ];

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
