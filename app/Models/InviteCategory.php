<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InviteCategory extends Model
{
    /**
     * @var string[]
     */
    protected $fillable=['invitecategoryable_id', 'invitecategoryable_type','percent'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function invitecategoryable()
    {
        return $this->morphTo();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function userInvites()
    {
        return $this->hasMany(UserInvite::class);
    }

}
