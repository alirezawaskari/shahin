<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AttributeGroup extends Model
{
    use SoftDeletes;

    /**
     * @var string[]
     */
    protected $dates = ['deleted_at'];
    /**
     * @var string[]
     */
    protected $fillable=['title','category_id'];

    /**
     * @param $id
     * @return string
     */
    public static function title($id)
    {
        $attributeGroup=AttributeGroup::find($id);
        if($attributeGroup){ return $attributeGroup->title;}
        return '';
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function attributes()
    {
        return $this->hasMany(Attribute::class,'attribute_group_id','id');
    }
}
