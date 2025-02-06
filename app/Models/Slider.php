<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Slider extends Model
{
    use SoftDeletes;

    /**
     * @var string[]
     */
    protected $dates = ['deleted_at'];
    /**
     * @var string[]
     */
    protected $fillable=['img','title','link','type','category_id','representation_id'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
    */
    public function sliderable()
    {
        return $this->morphTo();
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function representation()
    {
        return $this->belongsTo(Representation::class);
    }
      /**
     * @param $query
     * @return mixed
     */
    public function scopeFilter($query)
    {
        $query->where('sliderable_id' , null);

        return $query;
    }
}
