<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class About extends Model
{
    use SoftDeletes;
    protected $dates = ['deleted_at'];
    protected $fillable=['image_two','image_three','image_four','image_five','content','content2',
        'content_one','content_two','content_three','content_four','content_five'];
}
