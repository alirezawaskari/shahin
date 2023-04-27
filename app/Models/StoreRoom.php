<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreRoom extends Model
{
    /**
     * @var array
     */
    protected $guarded = [];
    /**
     * @var string[]
     */
    protected $fillable=['warehouse_keeper_id','parent_id','name','manager','code','address','representation_id','lat','lon'];


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function representation()
    {
        return $this->belongsTo(Representation::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function warehouse_keeper()
    {
        return $this->belongsTo(User::class , 'warehouse_keeper_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function product_storeRoom()
    {
        return $this->hasMany(ProductStoreRoom::class);
    }

    /**
     * @param $id
     * @param $field
     * @return string
     */
    public static function getStoreRoom($id, $field)
    {
        if($id){ $store_room=StoreRoom::find($id); if($store_room){ return $store_room->$field;} }
        return '';
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
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

    /**
     * @param $productattributeable_id
     * @return string|null
     */
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
