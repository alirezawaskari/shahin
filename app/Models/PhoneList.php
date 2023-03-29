<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PhoneList extends Model
{
    protected $guarded = [];

    public function phones()
    {
        return $this->hasMany(Phone::class);
    }

    public function groupsmses()
    {
        return $this->hasMany(Groupsms::class);
    }
}
