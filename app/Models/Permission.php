<?php

namespace App\Models;

use Laratrust\Models\Permission as LaratrustPermission;

class Permission extends LaratrustPermission
{
    protected $guarded = [];

    /**
     * Get the phone associated with the user.
     */
    public function categoryPermissions()
    {
        return $this->belongsTo(CategoryPermission::class, 'cat_id');
    }

}
