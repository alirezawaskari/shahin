<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmsCmsContent extends Model
{
    protected $table = 'sms_cms_contents';

    protected $fillable = [
        'key',
        'value',
        'updated_by',
    ];
}
