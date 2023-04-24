<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AskedQuestion extends Model
{
    use SoftDeletes;

    /**
     * @var string[]
     */
    protected $dates = ['deleted_at'];

    /**
     * @var string[]
     */
    protected $fillable=['askedquestionable_id','askedquestionable_type','question','answer'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function askedquestionable()
    {
        return $this->morphTo();
    }

}
