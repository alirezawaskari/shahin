<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CategoryStoreRoom extends Model
{
    protected $guarded = [];
    use SoftDeletes;
    protected $dates = ['deleted_at'];
    protected $fillable=['title'];

    public function representation()
    {
        return $this->belongsTo(Representation::class);
    }

    public static function getCategoryStoreRoom($id,$field)
    {
        if($id){ $cat=CategoryStoreRoom::find($id); if($cat){ return $cat->$field;} }
        return '';
    }

}
