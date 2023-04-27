<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VipUser extends Model
{
    /**
     * @var array
     */
    protected $guarded = [];

    /**
     *
     */
    const TYPE = [
        'VIP' => 'کاربر ویژه',
        'SOS' => 'کاربر نیازمند کمک',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class , 'user_id');
    }
}
