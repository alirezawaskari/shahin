<?php

namespace App\Models;

use App\Helpers\Helper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Request extends Model
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];
    protected $fillable=['user_id','card_id','file','store_room_id','type','status','confirmator','sender_id','description'];

    const STATUS = [
        '1' => 'پیش نویس',
        '2' => 'در انتظار تایید انباردار',
        '3' => 'در انتظار ارسال',
        '4' => 'رد شده',
        '5' => 'ارسال شده',
        '6' => 'دریافت شد',
    ];


    public function requestItems()
    {
        return $this->belongsToMany(Product::class,'request_items')->withPivot('number','product_attribute_id');
    }
	
	public function storeRoom()
    {
        return $this->belongsTo(StoreRoom::class);
    }

    public function changeStoreRoom()
    {
        return $this->hasOne(ChangeStoreRoom::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }


	public static function stock($request_id,$product_id,$product_attribute_id)
    {
        $request=Request::find($request_id);
        $product=Product::select('id','internal_use')->where('id',$product_id)->first();

        if($request->type == 0 || $product->internal_use == 1){
            $store_rooms_stock=$product->storeRooms()
                ->where('store_rooms.id', $request->store_room_id)->sum('stock');
            if(isset($store_rooms_stock)) return $store_rooms_stock;
        }
        if($request->type == 1 || $product->internal_use == 0){
            $product_attributes_stock = $product->attributes()
                ->where('product_attributes.id', $product_attribute_id)->sum('stock');
            if(isset($product_attributes_stock)) return $product_attributes_stock;
        }

        return null;
    }
	
	public static function brandProductInRequest($product_id)
    {
        $brand_title='------';
        $product=Product::select('id','brand_id')->where('id',$product_id)->first();
        if($product->brand_id != null) $brand_title = Brand::find($product->brand_id)->title;
        return $brand_title;
    }

    public static function requestByField($request_id,$field)
    {
        $request_rec = Request::find($request_id);
        if($request_rec != null){
            $fullName = Helper::user($request_rec->$field,'name') . ' '. Helper::user($request_rec->$field,'family');
            return $fullName;
        }
        return null;
    }
}
