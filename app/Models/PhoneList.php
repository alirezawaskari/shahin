<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PhoneList extends Model
{
    /**
     * @var array
     */
    protected $guarded = [];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function phones()
    {
        return $this->hasMany(Phone::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function groupsmses()
    {
        return $this->hasMany(Groupsms::class);
    }
}
