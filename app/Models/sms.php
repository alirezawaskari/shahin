<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class sms extends Model
{
    /**
     * @var array
     */
    protected $guarded = [];


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function callcenter()
    {
        return $this->belongsTo(Callcenter::class , 'sms_id');
    }

}
