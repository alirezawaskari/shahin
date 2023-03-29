<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AskedQuestion extends Model
{
    use SoftDeletes;
    protected $dates = ['deleted_at'];

    protected $fillable=['askedquestionable_id','askedquestionable_type','question','answer'];

    public function askedquestionable()
    {
        return $this->morphTo();
    }

}
