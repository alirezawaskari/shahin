<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FactorItemAttributes extends Model
{
    protected $guarded=['id'];
    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function factorItemAttributable()
    {
        return $this->morphTo();
    }
}
