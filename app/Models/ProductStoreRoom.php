<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class ProductStoreRoom extends Model
{
    use SoftDeletes;

    protected $guarded = [];
	
    protected $dates = ['deleted_at' , 'created_at' , 'updated_at' , 'expire_date'];

    protected $table = 'product_store_room';

	const STORAGE = [
        '0' => 'قفسه',
        '1' => 'یخچال',
    ];
	
    public function purchase_invoice()
    {
        return $this->belongsTo(PurchaseInvoice::class);
    }

    public function product_storeRoom()
    {
        return $this->belongsTo(Product::class);
    }

    public function main_unit()
    {
        return $this->belongsTo(CountingUnit::class, 'main_unit_id');
    }

    public function sub_unit()
    {
        return $this->belongsTo(CountingUnit::class, 'sub_unit_id');
    }

	public function product()
    {
        return $this->belongsTo(Product::class);
    }
	
    public function store_room()
    {
        return $this->belongsTo(StoreRoom::class);
    }

    public function category_store_room()
    {
        return $this->belongsTo(CategoryStoreRoom::class);
    }

    public function scopeFilter($query)
    {
        $name = request('name');
        $startDate = request('start_date');
        $endDate = request('end_date');
        $storage = request('storage');
        $main_unit = request('main_unit');
        $category_store_room = request('category_store_room');
        $expired_products = request('expired_products');
        $expiring_products = request('expiring_products');
        $finishing_products = request('finishing_products');
        if (isset($name) && $name != '') {
            $query->whereHas('product', function ($query) use ($name) {
                $query->where('name', 'LIKE', '%' . $name . '%');
            });
        }
        if (isset($main_unit) && $main_unit != 'all') {
            $query->whereHas('main_unit', function ($query) use ($main_unit) {
                $query->where('id', $main_unit);
            });
        }
        if (isset($storage) && $storage != 'all') {
            $query->where('storage', $storage);
        }

        if (isset($category_store_room) && $category_store_room != 'all') {
            $query->where('category_store_room_id', $category_store_room);
        }

        if (isset($startDate) && trim($startDate) != '' && isset($endDate) && trim($endDate) != '') {
            $query->whereDate('expire_date', '>=', $startDate)->whereDate('expire_date', '<=', $endDate);
        }

        if ($expired_products) {
            $query->where('expire_date' , '<=' , Carbon::now());
        }

        if ($expiring_products) {
            $query->where('expire_date' , '<' , Carbon::now()->addDays('expire_alert'))->where('expire_date' , '<' , Carbon::now());
        }

        if ($finishing_products) {
            $query->whereRaw("order_point >=  stock");
        }
        return $query;
    }

    protected static function productStoreRoomExpiring()
    {
        $product_storeRoom_expiring = [];
        if(auth()->user()->store_room)
        {
            $store_room = auth()->user()->store_room;
            $store_room_products = $store_room->product_storeRoom;
            foreach($store_room_products as $item)
            {
                if($item->expire_date && $item->expire_date->subDays($item->expire_alert) <= Carbon::now() && $item->expire_date <= Carbon::now())
                {
                    $product_storeRoom_expiring[] = $item;
                }
            }
        }
        return $product_storeRoom_expiring;
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

