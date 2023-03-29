<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Cviebrock\EloquentSluggable\Sluggable;
//use Conner\Tagging\Taggable;

class News extends Model
{
    use Sluggable , SoftDeletes;
//    use Taggable ;
    protected $dates = ['deleted_at'];
    protected $fillable=['user_id','category_news_id','title','subject','slug','image','description','status'];
    public function sluggable(): array{  return ['slug' => ['source' => 'title']];}

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
