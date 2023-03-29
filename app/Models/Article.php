<?php

namespace App\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Article extends Model
{
    use Sluggable,SoftDeletes;
    protected $guarded = [];
    protected $dates = ['deleted_at'];
    protected $fillable=['user_id','category_article_id','title','slug','image','summary',
        'description','status'];


    public function sluggable():array
    {
        return [
            'slug' => [
                'source' => 'title'
            ]
        ];
    }

    public function path()
    {
        return "/articles/$this->slug";
    }


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category_article()
    {
        return $this->belongsTo(CategoryArticle::class);
    }

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
