<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    protected $fillable = [
        'name',
        'subject',
        'keyword',
        'content',
        'image',
        'image_header',
        'start_date',
        'end_date',
    ];
}
