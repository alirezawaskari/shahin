<?php

namespace App\Models;

use Laratrust\Models\Role as LaratrustRole;

class Role extends LaratrustRole
{
    /**
     * @var array
     */
    protected $guarded = [];
    /**
     *
     */
    const CATEGORIES = [
        'employee' => 'کارمند',
        'manager' => 'مدیر',
    ];
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function card()
    {
        return $this->belongsTo(Card::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    public function scopeFilter($query)
    {
        $name = request('name');

        if (isset($name) && $name != 'all') {
            $query->where('display_name', 'LIKE', '%' . $name . '%');
        }
        return $query;
    }
}
