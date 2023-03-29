<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategoryPermission extends Model
{
    protected $guarded = [];

    public function permission()
    {
        return $this->belongsTo(Permission::class);
    }

}
