<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductAttribute extends Model
{
    use SoftDeletes;
    protected $fillable=['attribute_id','attribute_item_id','value','product_id','price','productattributeable_id'
        ,'productattributeable_type','initial_stock','stock','guarantee','posted_by','is_show','submission_time','seller_code'];

    const GUARANTEE= [
        '1' => 'گارانتی اصالت و سلامت فیزیکی کالا'
    ];

    public function productattributeable()
    {
        return $this->morphTo();
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function factorItemProducts()
    {
        return $this->hasMany(ProductAttribute::class);
    }

    public function attributes()
    {
        return $this->hasMany(Attribute::class);
    }

    public function attributeItem()
    {
        return $this->hasMany(AttributeItem::class);
    }

    public function couponItems()
    {
        return $this->morphMany(CouponItem::class, 'couponitemable');
    }

    public static function hasProductAttribute($attribute_item_id,$product_id)
    {
        $product_attribute = ProductAttribute::where('attribute_item_id',$attribute_item_id)->where('product_id',$product_id)->count();
        if($product_attribute > 0)
            return true;
        else
            return false;
    }

    public static function showProductAttribute($attribute_item_id,$product_id)
    {
        $product_attribute = ProductAttribute::where('attribute_id',$attribute_item_id)
            ->where('product_id',$product_id)->first();
        if($product_attribute)
            return $product_attribute->value;
        else
            return null;
    }

    public static function minPriceProductAttributeActive($product_id)
    {
        $price=0;
        if($product_id){
            $price=ProductAttribute::where('product_id',$product_id)->where('is_show',1)->min('price');
        }
        return $price;
    }

    public static function productAttributeActive($product_id)
    {
        $count=0;
        if($product_id){
            $count=ProductAttribute::where('product_id',$product_id)->where('is_show',1)->count();
        }
        return $count;
    }

    public static function findStoreRoomName($id)
    {
        $p_a = ProductAttribute::find($id);
        $product_store_room = ProductStoreRoom::find($p_a->productattributeable_id);
        if($p_a){
            $store_room_name =
                StoreRoom::find($product_store_room->store_room_id)->name .'  (  '. $product_store_room->id .':'. number_format($product_store_room->sales_price) .' )  ';

            return $store_room_name;
        }
    }

}
