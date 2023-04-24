<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Cviebrock\EloquentSluggable\Sluggable;


class News extends Model
{
    use Sluggable , SoftDeletes;
//    use Taggable ;
    /**
     * @var string[]
     */
    protected $dates = ['deleted_at'];
    /**
     * @var string[]
     */
    protected $fillable=['user_id','category_news_id','title','subject','slug','image','description','status'];

    /**
     * @return \string[][]
     */
    public function sluggable(): array{  return ['slug' => ['source' => 'title']];}

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
