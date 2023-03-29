<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Phone extends Model
{
    protected $guarded = [];


    public function phone_list()
    {
        return $this->belongsTo(PhoneList::class);
    }
}
