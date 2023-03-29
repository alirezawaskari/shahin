<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Province extends Model
{
    protected $guarded = [];

    public function citys()
    {
        return $this->hasMany(City::class);
    }
    public function users()
    {
        return $this->hasMany(User::class);
    }

}
