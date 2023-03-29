<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class sms extends Model
{
    protected $guarded = [];


    public function callcenter()
    {
        return $this->belongsTo(Callcenter::class , 'sms_id');
    }

}
