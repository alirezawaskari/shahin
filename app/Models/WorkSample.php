<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkSample extends Model
{
    use SoftDeletes;

    /**
     * @var string[]
     */
    protected $fillable=['worksampleable_id','worksampleable_type','title','image','status', 'center_id'];

    /**
     * @var string[]
     */
    protected $dates = ['deleted_at'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function worksampleable()
    {
        return $this->morphTo();
    }
    public function center()
    {
        return $this->belongsTo(Center::class);
    }
}
