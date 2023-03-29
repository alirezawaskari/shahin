<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreRoom extends Model
{
    protected $guarded = [];
    protected $fillable=['warehouse_keeper_id','parent_id','name','manager','code','address','representation_id','lat','lon'];


    public function representation()
    {
        return $this->belongsTo(Representation::class);
    }

    public function warehouse_keeper()
    {
        return $this->belongsTo(User::class , 'warehouse_keeper_id');
    }

    public function product_storeRoom()
    {
        return $this->hasMany(ProductStoreRoom::class);
    }

    public static function getStoreRoom($id,$field)
    {
        if($id){ $store_room=StoreRoom::find($id); if($store_room){ return $store_room->$field;} }
        return '';
    }
    public function products()
    {
        return $this->belongsToMany(Product::class)
            ->withPivot('id','user_id','category_store_room_id'
                ,'storage_store_room_id','row_num','col_num','discount','order_point','order_day'
                ,'expire_alert','expire_date','measurement_unit_id','storage_num',
                'purchase_type','pay_type', 'stock', 'sales_price', 'purchase_price',
                'barcode','main_unit_id', 'sub_unit_id','main_unit_value','sub_unit_value','purchase_invoice_id' ,'initial_stock')
            ->withTimestamps();
    }

    public static function storeRoomName($productattributeable_id)
    {
        $product_store_room = ProductStoreRoom::find($productattributeable_id);
        if($product_store_room){
            $store_room = $product_store_room->store_room ;
            $store_room_name = $store_room->name .'  (  '. $product_store_room->id .':'. number_format($product_store_room->sales_price) .' )  ';
            return $store_room_name;
        }
        return null;

    }


}
