<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChangeStoreRoom extends Model
{
    use SoftDeletes;
    protected $dates = ['deleted_at'];
    protected $fillable=['product_id','request_id','changestoreroomable_id','changestoreroomable_type','number','user_id','type'];

    const TYPE = [
        '0' => 'خروج',
        '1' => 'ورود',
    ];

    public function changestoreroomable()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function request()
    {
        return $this->belongsTo(Request::class);
    }

    public static function createChangeStoreRoom($product_id, $changestoreroomable_id, $changestoreroomable_type, $number, $type)
    {
        $has_change = ChangeStoreRoom::where('product_id', $product_id)->where('changestoreroomable_id', $changestoreroomable_id)
            ->where('changestoreroomable_type', $changestoreroomable_type)
            ->where('number', $number)->where('user_id', \Auth::user()->id)->where('type', $type)->count();
        if ($has_change == 0) {
            ChangeStoreRoom::create(['product_id' => $product_id, 'changestoreroomable_id' => $changestoreroomable_id
                , 'changestoreroomable_type' => $changestoreroomable_type, 'number' => $number, 'user_id' => \Auth::user()->id, 'type' => $type]);
        }
    }


    public static function storeRoomProductAddress($storeRoom)
    {
        $request_rec = Request::find($storeRoom->request_id);
        if(Product::find($storeRoom->product_id) && Product::find($storeRoom->product_id)->storeRooms){
            $store_room_product = Product::find($storeRoom->product_id)->storeRooms()
                ->wherePivot('store_room_id',$storeRoom->changestoreroomable_id)->first();
        }
        if(isset($store_room_product) && isset($store_room_product->pivot)){
            $store_room_address = $store_room_product->pivot->storage_num.' - '.$store_room_product->pivot->row_num.' - '.$store_room_product->pivot->col_num ;
            return $store_room_address;
        }
        return null;
    }
}
