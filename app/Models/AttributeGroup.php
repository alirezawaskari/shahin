<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AttributeGroup extends Model
{
    use SoftDeletes;
    protected $dates = ['deleted_at'];
    protected $fillable=['title','category_id'];

    public static function title($id)
    {
        $attributeGroup=AttributeGroup::find($id);
        if($attributeGroup){ return $attributeGroup->title;}
        return '';
    }

    public function attributes()
    {
        return $this->hasMany(Attribute::class,'attribute_group_id','id');
    }
}
