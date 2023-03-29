<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CenterCategory extends Model
{
    protected $guarded = [];

    public function centers()
    {
        return $this->morphMany(Center::class, 'center_category_id');
    }
    
}
