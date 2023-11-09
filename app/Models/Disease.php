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
        $title=request()->title;

        if (isset($title) && trim($title) != '') {
            $query->where('title', 'LIKE', '%' . $title. '%');
        }

        return $query;
    }
}
