<?php

namespace App\Http\Controllers\Admin;

use App\Models\StoreRoom;
use App\Models\CountingUnit;
use App\Models\ProductStoreRoom;
use App\Http\Controllers\Controller;
use App\Helpers\Helper;
use App\Models\CategoryStoreRoom;
use App\Models\Product;
use App\Models\StorageStoreRoom;
use Illuminate\Http\Request;

class ProductStoreRoomController extends Controller
{


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if($id){
            $product =Product::find($id);
            $product_store_rooms = $product->storeRooms()->paginate(15);
            foreach ($product_store_rooms as $item) {
                $item['category_store_room_title']=CategoryStoreRoom::getCategoryStoreRoom($item->pivot->category_store_room_id,'title');
                $item['store_room_name']=StoreRoom::getStoreRoom($item->pivot->store_room_id,'name');
                if($item->pivot->purchase_type == 0) $item['purchase_type']='آنلاین';
                elseif($item->pivot->purchase_type == 1) $item['purchase_type']='حضوری';
                elseif($item->pivot->purchase_type == 2) $item['purchase_type']='هر دو ';

                if($item->pivot->pay_type == 0) $item['pay_type']='نقدی';
                elseif($item->pivot->pay_type == 1) $item['pay_type']='اقساط';
                elseif($item->pivot->pay_type == 2) $item['pay_type'] =' هر دو';

                $item['main_unit_title']=CountingUnit::getCountingUnit($item->pivot->main_unit_id,'title') ;
                $item['sub_unit_title']=CountingUnit::getCountingUnit($item->pivot->sub_unit_id,'title') ;

                $item['storage_store_room_title'] = StorageStoreRoom::getStorageStoreRoom($item->pivot->storage_store_room_id,'title');
            }

            return view('Admin.productStoreRoom.list', compact('product','product_store_rooms'));
        }

        return redirect()->back();
    }

    public function productStoreRoomByProductId($product_id,$id)
    {
        if ($id && $product_id) {
            $product=Product::find($product_id);
            if($product){
                $type_product=Product::find($product_id)->type;
                $category_store_rooms = CategoryStoreRoom::all();
                $storage_store_rooms = StorageStoreRoom::all();

                if(\Auth::user()->isAbleTo('product-detail')) {
                    $pro_store_rooms = $product->storeRooms()->wherePivot('id',$id)->first();
                }

                $store_rooms = StoreRoom::all();
                $counting_units = CountingUnit::where('type','packing')->get();
                $counting_units_measurement = CountingUnit::where('type','measurement')->get();

                return view('Admin.productStoreRoom.edit', compact('category_store_rooms',
                    'counting_units','store_rooms','pro_store_rooms','product_id','storage_store_rooms',
                    'counting_units_measurement','type_product'));
            }

        }
        return redirect()->back();
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if (\Auth::user()->isAbleTo('product-detail')) {

            if($request->sales_price > 0)$request['sales_price'] = str_replace(",","",$request->sales_price);
            if($request->purchase_price > 0)$request['purchase_price'] = str_replace(",","",$request->purchase_price);
            if($request->discount > 0)$request['discount'] = str_replace(",","",$request->discount);

            $product=Product::find($request->product_id);

            // Product with StoreRoom
            if (isset($request->store_room_id) && $request->store_room_id != '') {

                $store_rooms = $product->storeRooms()->where('product_store_room.id',$id)->first();

                if(isset($request->stock))  $stock = $request->stock ;  else $stock = null;

                if (isset($store_rooms) && $store_rooms != '') {
                    $expire_date = Helper::convert($request->expire_date);
                    $store_rooms->pivot->update(
                        [
                            'category_store_room_id' => $request->category_store_room_id,
                            'storage_store_room_id' => $request->storage_store_room_id,
                            'row_num' => $request->row_num,
                            'col_num' => $request->col_num,
                            'discount' => $request->discount,
                            'order_point' => $request->order_point,
                            'order_day' => $request->order_day,
                            'expire_alert' => $request->expire_alert,
                            'expire_date' => $expire_date,
                            'measurement_unit_id' => $request->measurement_unit_id,
                            'purchase_type' => $request->purchase_type,
                            'pay_type' => $request->pay_type,
                            'stock' => $stock,
                            'sales_price' => $request->sales_price,
                            'purchase_price' => $request->purchase_price,
                            'barcode' => $request->barcode,
                            'main_unit_id' => $request->main_unit_id,
                            'sub_unit_id' => $request->sub_unit_id,
                            'main_unit_value' => $request->main_unit_value,
                            'sub_unit_value' => $request->sub_unit_value,
                            'storage_num' => $request->storage_num
                        ]);
                }
                else {
                    $product->storeRooms()->attach($request->store_room_id,
                        [
                            'category_store_room_id' => $request->category_store_room_id,
                            'storage_store_room_id' => $request->storage_store_room_id,
                            'row_num' => $request->row_num,
                            'col_num' => $request->col_num,
                            'discount' => $request->discount,
                            'order_point' => $request->order_point,
                            'order_day' => $request->order_day,
                            'expire_alert' => $request->expire_alert,
                            'expire_date' => $request->expire_date,
                            'measurement_unit_id' => $request->measurement_unit_id,
                            'purchase_type' => $request->purchase_type,
                            'pay_type' => $request->pay_type,
                            'stock' => $stock,
                            'sales_price' => $request->sales_price,
                            'purchase_price' => $request->purchase_price,
                            'barcode' => $request->barcode,
                            'main_unit_id' => $request->main_unit_id,
                            'sub_unit_id' => $request->sub_unit_id,
                            'main_unit_value' => $request->main_unit_value,
                            'sub_unit_value' => $request->sub_unit_value,
                            'storage_num' => $request->storage_num
                        ]);
                }

            }

            return redirect()->back()->with(['update' => 'success']);

        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request,$id)
    {
        if ($id) {
            $product = Product::find($id);
            if (isset($product)) {
                if(isset($product->storeRooms)){
                    $store_room=$product->storeRooms()->wherePivot('id',$request->product_store_room_id)->first();
                    if(isset($store_room->pivot))
                        $store_room->pivot->delete();
                }
                $product_storeRooms = $product->storeRooms->count();
                $product_centers = $product->centers->count();

                if($product_storeRooms == 0 && $product_centers == 0){
                    $product->isShow=0;
                    $product->save();
                    return redirect()->back()->with(['delete' => 'success']);
                }
            }
        }
        return redirect()->back();
    }


	public function filter(StoreRoom $storeRoom)
    {
        $main_units = CountingUnit::where('type' , 'packing')->get();
        $store_room_products = $storeRoom->filter();

		return view('Admin.storeroom.product', compact('store_room_products','storeRoom' , 'main_units'));
    }

	public function deleteProductStoreRoom($id)
    {
        $product_storeRoom = ProductStoreRoom::find($id);
        $product = $product_storeRoom->product;
        $product_storeRoom->delete();
        $product->product_storeRoom ? '' : $product->delete();
        return redirect()->back();
    }
}
