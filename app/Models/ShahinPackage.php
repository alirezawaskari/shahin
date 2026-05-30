<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShahinPackage extends Model
{
    protected $table = 'shahin_packages';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'status',
        'features',
    ];

    protected $casts = [
        'features' => 'array',
    ];
    /**
     * 🔗 ارتباط با permission ها
     */
    public function categoryPermission()
    {
        return $this->belongsToMany(
            CategoryPermission::class,
            'category_permissions_shahin_packages',
            'shahin_package_id',
            'category_permission_id'
        );
    }

    public function hasCategoryPermission($permissionName)
    {
        return $this->categoryPermission()->where('name', $permissionName)->exists();
    }
    /**
     * 🔗 ارتباط با ghorfe_online_lists
     */
    public function ghorfeLists()
    {
        return $this->hasMany(GhorfeOnlineList::class, 'shahin_package_id');
    }
}
