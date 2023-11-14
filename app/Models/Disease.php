<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Disease extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $guarded=['id'];
    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    /**
     * @param $query
     * @return mixed
     */
    public function scopeFilter($query)
    {
        $name=request()->name;

        if (isset($name) && trim($name) != '') {
            $query->where('name', 'LIKE', '%' . $name. '%');
        }

        return $query;
    }
}
