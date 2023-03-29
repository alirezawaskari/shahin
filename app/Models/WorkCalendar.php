<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkCalendar extends Model
{
    use SoftDeletes;
    protected $fillable=['center_id','date','date_en','user_id','time_intervals','start_time','end_time'];

    protected $dates = ['deleted_at'];

}
