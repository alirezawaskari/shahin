<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Entry extends Model
{
    protected $guarded = [];

    public function entryable()
    {
        return $this->morphTo();
    }
}
