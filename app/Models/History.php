<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class History extends Model
{
    /**
     * @var array
     */
    protected $guarded = [];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function historiable()
    {
        return $this->morphTo();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function reason()
    {
        return $this->belongsTo(Reason::class);
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function callcenter()
    {
        return $this->belongsTo(User::class , 'callcenter_id');
    }

    //get status history

    /**
     * @param $status
     * @return string
     */
    protected static function status($status)
    {
        switch($status)
        {
            case 0:
                return 'لینک ارسال شده';
                break;
            case 1:
                return 'تعلیق شده';
                break;
            default:
            return 'انتقال به همکار دیگر';
        }
    }
}
