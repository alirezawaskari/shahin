<?php

namespace App\Models;

use Laratrust\Models\Permission as LaratrustPermission;

class Permission extends LaratrustPermission
{
    protected $guarded = [];

    /**
     * Laratrust's Permission declares $fillable = ['name', 'display_name', 'description'],
     * and a child's $guarded = [] does not undo that: Eloquent checks a non-empty $fillable
     * first, so every column this project added afterwards — link, isMenu, cat_id, position,
     * is_active — was silently dropped from update() and create(). Saving a sub-menu's link
     * or ترتیب from تنظیمات منو appeared to work and changed nothing.
     */
    protected $fillable = [
        'name',
        'display_name',
        'description',
        'link',
        'isMenu',
        'cat_id',
        'position',
        'is_active',
    ];

    /**
     * Get the phone associated with the user.
     */
    public function categoryPermissions()
    {
        return $this->belongsTo(CategoryPermission::class, 'cat_id');
    }

}
