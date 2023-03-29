<?php

namespace App\Models;

use Laratrust\Models\LaratrustRole;

class Role extends LaratrustRole
{
    protected $guarded = [];

    public function card()
    {
        return $this->belongsTo(Card::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class);
    }
}
