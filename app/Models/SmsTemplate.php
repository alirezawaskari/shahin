<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmsTemplate extends Model
{
    /**
     * @var array
     */
    protected $fillable = [
        'user_id',
        'name',
        'fa_name',
        'content',
        'status',
    ];
    protected $guarded = [];

     /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class , 'user_id');
    }

    /**
     * @param $query
     * @return mixed
     */
    public function scopeFilter($query)
    {
        $name = request('name');
        $status = request('status');

        if (isset($name) && trim($name) != '') {
            $query->where('name', 'LIKE', '%' . $name . '%');
        }

        if (isset($status) && trim($status) != 'all') {
            $query->where('status', $status);
        }

        return $query;
    }
}
