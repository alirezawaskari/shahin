<?php

namespace App\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Article extends Model
{
    use Sluggable,SoftDeletes;

    /**
     * @var array
     */
    protected $guarded = [];
    /**
     * @var string[]
     */
    protected $dates = ['deleted_at'];
    /**
     * @var string[]
     */
    protected $fillable=['user_id','category_article_id','title','slug','image','summary',
        'description','status'];


    /**
     * @return \string[][]
     */
    public function sluggable():array
    {
        return [
            'slug' => [
                'source' => 'title'
            ]
        ];
    }

    /**
     * @return string
     */
    public function path()
    {
        return "/articles/$this->slug";
    }


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function category_article()
    {
        return $this->belongsTo(CategoryArticle::class);
    }

    /**
     * @param $status
     * @return string
     */
    public static function status($status)
    {
        switch ($status) {
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

}
