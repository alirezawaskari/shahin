<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VipUser extends Model
{
    protected $guarded = [];

    const TYPE = [
        'VIP' => 'کاربر ویژه',
        'SOS' => 'کاربر نیازمند کمک',
    ];
    
    public function user()
    {
        return $this->belongsTo(User::class , 'user_id');
    }
}
