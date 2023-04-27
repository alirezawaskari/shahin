<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Work extends Model
{
    /**
     * @var array
     */
    protected $guarded = [];

    use SoftDeletes;

    /**
     * @var string[]
     */
    protected $dates = ['deleted_at'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @param $query
     * @return mixed
     */
    public function scopeFilter($query)
    {
        $date = request('time');
        $user = request('user');

        if (isset($user) && trim($user) != '' && $user != 'all') {
            $query->where('user_id', $user);
        }

        if (isset($date) && trim($date) != '') {
            $query->whereDate('created_at', Carbon::createFromTimestamp($date / 1000)->toDateString());
        }

        return $query;
    }

}
