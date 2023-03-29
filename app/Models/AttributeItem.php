<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AttributeItem extends Model
{
    use SoftDeletes;
    protected $dates = ['deleted_at'];

    protected $fillable=['attribute_id','title','status'];

    public function attribute()
    {
        return $this->belongsTo(Attribute::class);
    }

    public function attributeItemColor()
    {
        return $this->hasOne(AttributeItemColor::class);
    }

    public function ProductAttribute()
    {
        return $this->belongsTo(ProductAttribute::class);
    }

    public static function getAttributeItem($id,$field)
    {
        if($id){
            $attributeItem = AttributeItem::find($id);
            return $attributeItem->$field;
        }
        return '';
    }

    public static function findPaletteByItem($attribute_item_id)
    {
       $item_color= AttributeItemColor::where('attribute_item_id',$attribute_item_id)->first();
       if($item_color){
           $color=Color::find($item_color->color_id);
           if($color != null)return $color->color_palette ; }
       return '';
    }
}
