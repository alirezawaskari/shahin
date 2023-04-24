<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Attribute extends Model
{
    use SoftDeletes;

    /**
     * @var string[]
     */
    protected $dates = ['deleted_at'];
    /**
     * @var string[]
     */
    protected $fillable=['attribute_group_id','category_id','type','title','isFilter','is_price','is_show'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function attributeItems()
    {
        return $this->hasMany(AttributeItem::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function attributeGroup()
    {
        return $this->belongsTo(AttributeGroup::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * @param $attr_id
     * @return int|string
     */
    public static function hasPriceAttribute($attr_id)
    {
        if($attr_id){
            $attr=Attribute::find($attr_id);
            if($attr){
                if($attr->is_price == 1){ return 1; } else { return 0;}
            }
        }
        return '';
    }

    /**
     * @param $attr_id
     * @return bool
     */
    public static function hasColorPalette($attr_id)
    {
        $attr=Attribute::where('title','رنگ')->pluck('id');
        in_array(trim($attr_id) , $attr->toArray()) ? $hasPallet=true : $hasPallet=false;
        return $hasPallet;
    }


    /**
     * @param $cat_id
     * @return mixed
     */
    public static function catHasAttribute($cat_id)
    {
        $count=Attribute::where('category_id',$cat_id)->count();
        return $count;
    }

    /**
     * @param $cat_id
     * @param $field
     * @return null
     */
    public static function findCatAttr($cat_id, $field)
    {
        $attr=Attribute::where('category_id',$cat_id)->first();
        if($attr)return $attr->$field;
        else return null;
    }


    /**
     * @param $product
     * @return null
     */
    public static function attributeIsPriceByLastCat($product)
    {
        $category_id = Category::latestCategory($product);
        if($category_id){
            $count = Attribute::where('category_id',$category_id)->where('is_price',1)->count();
            return $count;
        }
        return null;
    }

    /**
     * @param $cat_id
     * @return string
     */
    public static function allowedAttributeInCategory($cat_id)
    {
        $attribute = Attribute::where('category_id',$cat_id)->where('is_price',1)->first();
        if (!is_null($attribute))
        return $attribute->title;
        return '';
    }

}
