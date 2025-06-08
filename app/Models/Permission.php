<?php

namespace App\Models;

use Laratrust\Models\Permission as LaratrustPermission;

class Permission extends LaratrustPermission
{
    protected $guarded = [];

    /**
     * Get the phone associated with the user.
     */
    public function category()
    {
        return $this->hasOne(CategoryPermission::class , 'id','cat_id');
    }
}
