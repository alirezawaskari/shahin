<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SessionUserPerformance extends Model
{
    /**
     * @var array
     */
    protected $guarded = [];


    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function sessions()
    {
        return $this->hasMany(Session::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function userPerformances()
    {
        return $this->hasMany(UserPerformance::class);
    }
}
