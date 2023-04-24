<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CenterCategory extends Model
{
    /**
     * @var array
     */
    protected $guarded = [];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function centers()
    {
        return $this->morphMany(Center::class, 'center_category_id');
    }

}
