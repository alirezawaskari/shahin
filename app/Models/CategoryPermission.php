<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategoryPermission extends Model
{
    /**
     * @var array
     */
    protected $guarded = [];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function permission()
    {
        return $this->belongsTo(Permission::class);
    }

    public function ghorfes()
    {
        return $this->belongsToMany(GhorfeOnlineList::class, 'category_ghorfe');
    }

    public function shahinPackages()
    {
        return $this->belongsToMany(
            ShahinPackage::class,
            'category_permissions_shahin_packages',
            'category_permission_id',
            'shahin_package_id'
        );
    }
}
