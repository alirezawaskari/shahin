<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InviteCategory extends Model
{
    protected $fillable=['invitecategoryable_id', 'invitecategoryable_type','percent'];

    public function invitecategoryable()
    {
        return $this->morphTo();
    }

    public function userInvites()
    {
        return $this->hasMany(UserInvite::class);
    }

}
