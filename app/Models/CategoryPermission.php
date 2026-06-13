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
        return $this->hasMany(Permission::class,'cat_id','id');
    }

    public function ghorfes()
    {
        return $this->belongsToMany(GhorfeOnlineList::class, 'category_ghorfe');
    }

    public function shahinPackages()
    {
        return $this->belongsToMany(
            ShahinPackage::class,
            'category_permission_shahin_package',
            'category_permission_id',
            'shahin_package_id'
        );
    }
}
