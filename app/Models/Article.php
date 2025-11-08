<?php

namespace App\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Article extends Model {
    use Sluggable, SoftDeletes;

    /**
    * @var array
    */
    protected $guarded = [];
    /**
    * @var string[]
    */
    protected $dates = [ 'deleted_at' ];
    /**
    * @var string[]
    */
    protected $fillable = [ 'user_id', 'category_article_id', 'title', 'slug_title', 'slug', 'old_image', 'summary',
    'description', 'status' ];

    /**
    * @return \string[][]
    */

    public function sluggable():array {
        return [
            'slug' => [
                'source' => 'slug_title',
                'onUpdate' => true,
            ]
        ];
    }

    /**
    * @return string
    */

    public function path() {
        return "/articles/$this->slug";
    }

    /**
    * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
    */

    public function user() {
        return $this->belongsTo( User::class );
    }
    /**
    * @return \Illuminate\Database\Eloquent\Relations\MorphTo
    */

    public function articleable() {
        return $this->morphTo();
    }
    /**
    * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
    */

    public function category_article() {
        return $this->belongsTo( CategoryArticle::class );
    }
    /**
    * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
    */

    public function representations() {
        return $this->belongsToMany( Representation::class );
    }
    /**
    * @param $status
    * @return string
    */
    public static function status( $status ) {
        switch ( $status ) {
            case 0:
            return 'پیش نویس';
            break;
            case 1:
            return 'منتشر شده';
            break;
            default:
            return 'پیش نویس';
        }
    }

    /**
    * @param $query
    * @return mixed
    */

    public function scopeFilter( $query ) {
        $title = request('title');
        $startDate = request('startDate');
        $endDate = request('endDate');

        $query->whereDoesntHave( 'articleable' );
        if (auth()->user() && auth()->user()->admin_representation_id ) {
            $articles = auth()->user()->admin_representation->articles();
        }
        if ( isset( $title ) && $$title != '' ) {
            $articles->where( 'title', 'like', '%'.$title.'%' );
        }
        if ( isset( $startDate ) && isset( $request->endDate ) ) {
            $articles->where( 'created_at', '>', $startDate )->where( 'created_at', '<', $endDate );
        }
        return $query;
    }
}
