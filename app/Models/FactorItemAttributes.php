<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FactorItemAttributes extends Model
{
    use SoftDeletes;
    protected $guarded=['id'];
    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function factorItemAttributable()
    {
        return $this->morphTo();
    }
}
