<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttributeGroup;
use App\Models\Category;
use App\Models\Center;
use App\Models\ChangeStoreRoom;
use App\Models\CouponItem;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\ProductStoreRoom;
use App\Models\StoreRoom;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PriceProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request['price'] = str_replace(',', '', $request->price);
        $product = Product::find($request->product_id);
        if ($product) {
            //store_manager
            if (User::checkManager() != null) {
                $center_id = User::checkManager();

                $products_by_centers = $product->centers()->where('center_id', $center_id)->first();

                if ($products_by_centers != null) {

                    $all_product_attributes = $product->attributes();

                    $has_item = $all_product_attributes->where('attribute_id', $request->attribute_id)
                        ->wherePivot('product_id', $request->product_id)
                        ->wherePivot('attribute_item_id', $request->attribute_item_id)
                        ->where('productattributeable_type', Center::class)
                        ->where('productattributeable_id', $center_id)->count();

                    $similar_record = $all_product_attributes->where('attribute_id', $request->attribute_id)
                        ->wherePivot('product_id', $request->product_id)->wherePivot('attribute_item_id', $request->attribute_item_id)
                        ->where('productattributeable_type', Center::class)->where('productattributeable_id', $center_id)
                        ->where('price', $request->price)->where('guarantee', $request->guarantee)
                        ->where('posted_by', $request->posted_by)
                        ->where('submission_time', $request->submission_time)->where('seller_code', $request->seller_code)
                        ->count();

                    if ($has_item == 1 && $similar_record == 1 && $product->reference_price >= $request->price) {

                        $p_a_update=ProductAttribute::where('attribute_id', $request->attribute_id)
                            ->where('product_id', $request->product_id)->where('attribute_item_id', $request->attribute_item_id)
                            ->where('productattributeable_type', Center::class)->where('productattributeable_id', $center_id)
                            ->where('price', $request->price)->where('guarantee', $request->guarantee)
                            ->where('posted_by', $request->posted_by)
                            ->where('submission_time', $request->submission_time)->where('seller_code', $request->seller_code)->first();

                        $new_stock = $request->stock + $p_a_update->stock;
                        $p_a_update->initial_stock = $new_stock;
                        $p_a_update->stock = $new_stock;
                        $p_a_update->save();
                        return response('200');

                    }

                    if ($has_item == 0 && $similar_record == 0 && $product->reference_price >= $request->price) {
                        ProductAttribute::create(['attribute_id' => $request->attribute_id
                            , 'attribute_item_id' => $request->attribute_item_id,
                            'value' => $request->attribute_item_text, 'product_id' => $request->product_id,
                            'price' => $request->price,
                            'productattributeable_id' => $center_id
                            , 'productattributeable_type' => Center::class,
                            'initial_stock'=>$request->stock ,'stock' => $request->stock,
                            'guarantee' => $request->guarantee, 'posted_by' => $request->posted_by,
                            'is_show' => $request->is_show,
                            'submission_time' => $request->submission_time,
                            'seller_code' => $request->seller_code,
                        ]);
                        $product->isShow = 1;
                        $product->save();
                        return response('true');
                    }
                    else {
                        return response('301');
                    }

                }


            }

            if (\Auth::user()->isAbleTo('product-detail')) {
                if (isset($request->center_id))
                    $products_by_centers = $product->centers()->whereIn('center_id', $request->center_id)->get();
                if ($request->product_store_room_id)
                    $product_store_rooms = ProductStoreRoom::whereIn('id',$request->product_store_room_id)->get();


                if (isset($products_by_centers) && count($products_by_centers) > 0) {
                    foreach ($products_by_centers as $products_by_center) {

                        $has_item = $product->attributes()->where('attribute_id', $request->attribute_id)
                            ->wherePivot('product_id', $request->product_id)
                            ->wherePivot('attribute_item_id', $request->attribute_item_id)
                            ->where('productattributeable_type', Center::class)
                            ->where('productattributeable_id', $products_by_center->id)->count();

                        if ($has_item == 0 && $product->reference_price >= $request->price) {
                            ProductAttribute::create(['attribute_id' => $request->attribute_id
                                , 'attribute_item_id' => $request->attribute_item_id,
                                'value' => $request->attribute_item_text, 'product_id' => $request->product_id,
                                'price' => $request->price,
                                'productattributeable_id' => $products_by_center->id
                                , 'productattributeable_type' => Center::class,
                                'initial_stock'=>$request->stock ,
                                'stock' => $request->stock,
                                'guarantee' => $request->guarantee
                                , 'posted_by' => $request->posted_by,
                                'is_show' => $request->is_show,
                                'submission_time' => $request->submission_time,
                                'seller_code' => $request->seller_code,
                            ]);
                            $product->isShow = 1;
                            $product->save();

                        }
                    }
                    return response('true');
                }

                if (isset($product_store_rooms) && count($product_store_rooms) > 0) {
                    foreach ($product_store_rooms as $product_store_room) {

                        $has_item_st = $product->attributes()->where('attribute_id', $request->attribute_id)
                            ->wherePivot('product_id', $request->product_id)
                            ->wherePivot('attribute_item_id', $request->attribute_item_id)
                            ->where('productattributeable_type', ProductStoreRoom::class)
                            ->where('productattributeable_id', $product_store_room->id)->count();

                        if ($has_item_st === 0 && $product->reference_price >= $request->price) {

                            ProductAttribute::create(['attribute_id' => $request->attribute_id
                                , 'attribute_item_id' => $request->attribute_item_id,
                                'value' => $request->attribute_item_text, 'product_id' => $request->product_id,
                                'price' => $request->price,
                                'productattributeable_id' => $product_store_room->id
                                , 'productattributeable_type' => ProductStoreRoom::class,
                                'initial_stock'=>$request->stock ,
                                'stock' => $request->stock,
                                'guarantee' => $request->guarantee
                                , 'posted_by' => $request->posted_by,
                                'is_show' => $request->is_show,
                                'submission_time' => $request->submission_time,
                                'seller_code' => $request->seller_code,
                            ]);
                            $product->isShow = 1;
                            $product->save();

                            ChangeStoreRoom::createChangeStoreRoom($request->product_id, $product_store_room->store_room_id,
                                StoreRoom::class, $request->stock, 1);

                        }

                    }
                    return response('true');
                }
            }

        }
        return response('false');
    }

    public function storeWithoutAttr(Request $request)
    {
        $request['price'] = str_replace(',', '', $request->price);
        $product = Product::find($request->product_id);
        if ($product) {

            //store_manager
            if (User::checkManager() != null) {
                $center_id = User::checkManager();
                $products_by_center = $product->centers()->where('center_id', $center_id)->first();
                if ($products_by_center != null && count($products_by_center) > 0) {

                    $has_item = $product->attributes()
                        ->wherePivot('product_id', $request->product_id)->wherePivot('price', $request->price)
                        ->where('productattributeable_type', Center::class)
                        ->where('productattributeable_id', $products_by_center->id)->count();

                    if ($has_item == 0 && $product->reference_price >= $request->price) {
                        if ($request->price > 0) $request['price'] = str_replace(",", "", $request->price);
                        ProductAttribute::create([
                            'product_id' => $request->product_id, 'price' => $request->price,
                            'productattributeable_id' => $products_by_center->id,
                            'productattributeable_type' => Center::class,
                            'initial_stock'=>$request->stock ,
                            'stock' => $request->stock,
                            'guarantee' => $request->guarantee,
                            'posted_by' => $request->posted_by,
                            'is_show' => $request->is_show,
                            'submission_time' => $request->submission_time,
                            'seller_code' => $request->seller_code,
                        ]);
                        $product->isShow = 1;
                        $product->save();
                    }

                }
                return response('true');
            }

            if (\Auth::user()->isAbleTo('product-detail')) {
                if (isset($request->center_id))
                    $products_by_centers = $product->centers()->whereIn('center_id', $request->center_id)->get();
                if ($request->product_store_room_id)
                    $product_store_rooms = ProductStoreRoom::whereIn('id',$request->product_store_room_id)->get();

                if (isset($products_by_centers) && count($products_by_centers) > 0) {
                    foreach ($products_by_centers as $products_by_center) {

                        $has_item = $product->attributes()
                            ->wherePivot('product_id', $request->product_id)
                            ->wherePivot('price', $request->price)
                            ->where('productattributeable_type', Center::class)
                            ->where('productattributeable_id', $products_by_center->id)->count();

                        if ($has_item == 0 && $product->reference_price >= $request->price) {
                            if ($request->price > 0) $request['price'] = str_replace(",", "", $request->price);
                            ProductAttribute::create([
                                'product_id' => $request->product_id, 'price' => $request->price,
                                'productattributeable_id' => $products_by_center->id,
                                'productattributeable_type' => Center::class,
                                'initial_stock'=>$request->stock ,
                                'stock' => $request->stock,
                                'guarantee' => $request->guarantee,
                                'posted_by' => $request->posted_by,
                                'is_show' => $request->is_show,
                                'submission_time' => $request->submission_time,
                                'seller_code' => $request->seller_code,
                            ]);
                            $product->isShow = 1;
                            $product->save();
                        }

                    }
                }

                if (isset($product_store_rooms) && count($product_store_rooms) > 0) {
                    foreach ($product_store_rooms as $product_store_room) {

                        $has_item = $product->attributes()
                            ->wherePivot('product_id', $request->product_id)->wherePivot('price', $request->price)
                            ->where('productattributeable_type', ProductStoreRoom::class)
                            ->where('productattributeable_id', $product_store_room->id)->count();

                        if ($has_item == 0 && $product->reference_price >= $request->price) {
                            if ($request->price > 0) $request['price'] = str_replace(",", "", $request->price);
                            ProductAttribute::create([
                                'product_id' => $request->product_id,
                                'price' => $request->price,
                                'productattributeable_id' => $product_store_room->id,
                                'productattributeable_type' => ProductStoreRoom::class,
                                'initial_stock'=>$request->stock ,
                                'stock' => $request->stock,
                                'guarantee' => $request->guarantee,
                                'posted_by' => $request->posted_by,
                                'is_show' => $request->is_show,
                                'submission_time' => $request->submission_time,
                                'seller_code' => $request->seller_code,
                            ]);
                        }

                        $product->isShow = 1;
                        $product->save();

                        ChangeStoreRoom::createChangeStoreRoom($request->product_id, $product_store_room->store_room_id,
                            StoreRoom::class, $request->stock, 1);

                    }
                }

                return response('true');
            }

        }
        return response('false');
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $product = Product::find($id);
        if ($product) {
            $cat_id = Category::latestCategory($product);
            $attributes = [];
            $center_products = $product->centers;
            $product_store_rooms = $product->storeRooms;

            foreach ($product_store_rooms as $store_room){
                $store_room['name'] = $store_room->name .'  (  '. $store_room->pivot->id .':'. number_format($store_room->pivot->sales_price) .' )  ';
            }

            if ($cat_id) {
                $attributeGroups = AttributeGroup::where('category_id', $cat_id)->get();
                foreach ($attributeGroups as $attribute_group) {
                    $attributes[$attribute_group->id] = $attribute_group->attributes()->where('is_price', 1)->get();
                }
            }
            $center_id = null;
            if (User::checkManager() != null) $center_id = User::checkManager();


            return view("Admin.product.price.create",
                compact('product', 'center_products', 'product_store_rooms', 'attributes', 'center_id'));

        }
        return redirect()->back();
    }

    public function pricesList($id)
    {
        $product = Product::find($id);
        if ($product) {
            if (\Auth::user()->isAbleTo('product-detail')) {

                if (User::checkManager() != null) {
                    $center_id = User::checkManager();
                    $product_attributes = Product::find($id)->attributes()
                        ->where('attributes.is_price', 1)->where('productattributeable_type', Center::class)
                        ->where('productattributeable_id', $center_id)->get();
                } else {
                    $product_attributes = Product::find($id)->attributes()->where('attributes.is_price', 1)->get();
                    foreach ($product_attributes as $product_attribute){
                        $product_attribute['store_room_name'] = StoreRoom::storeRoomName($product_attribute->pivot->productattributeable_id) ;
                    }
                }

                return view("Admin.product.price.lists", ['product_id' => $id, 'product_attributes' => $product_attributes]);
            }
        }

        return redirect()->back();
    }

    public function pricesListWithoutAttribute($id)
    {
        $product = Product::find($id);
        if ($product && User::checkManager() != null) {
            $product_attributes = ProductAttribute::where('product_id', $id)->where('price', '!=', 0)
                ->where('productattributeable_id', User::checkManager())->where('productattributeable_type', Center::class)->get();
            return view("Admin.product.price.listsWithoutAttribute", ['product_id' => $id, 'product_attributes' => $product_attributes]);
        }

        if ($product && \Auth::user()->isAbleTo('product-detail')) {
            $product_attributes = ProductAttribute::where('product_id', $id)->where('price', '!=', 0)->get();

            foreach ($product_attributes as $product_attribute){
                $product_attribute['store_room_name'] = StoreRoom::storeRoomName($product_attribute->pivot->productattributeable_id) ;
            }

            return view("Admin.product.price.listsWithoutAttribute", ['product_id' => $id, 'product_attributes' => $product_attributes]);
        }

        return redirect()->back();
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $product_attribute = ProductAttribute::find($id);
        if ($product_attribute) {
            $product = Product::find($product_attribute->product_id);
            return view("Admin.product.price.edit", ['product_attribute' => $product_attribute, 'product' => $product]);
        }
        return redirect()->back();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $request['price'] = str_replace(',', '', $request->price);
        $product_attribute = ProductAttribute::find($id);
        if ($product_attribute) {
            $product_attribute->update([
                'price' => $request->price,
                'initial_stock'=>$request->stock ,
                'stock' => $request->stock,
                'guarantee' => $request->guarantee,
                'posted_by' => $request->posted_by,
                'is_show' => $request->is_show,
                'submission_time' => $request->submission_time,
                'seller_code' => $request->seller_code,
            ]);
        }
        return redirect('/dashboard/product/pricesList/' . $product_attribute->product_id)
            ->with(['update' => 'success']);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    //Update several product_attribute fields
    public function updateProductAttribute(Request $request)
    {
        if(isset($request->product_attribute_id)){
            $product_attribute = ProductAttribute::find($request->product_attribute_id);
            if($product_attribute){
                if(isset($request->price)) {
                    $request['price'] = str_replace(',', '', $request->price);
                    $product_attribute->price=$request->price;
                }
                if(isset($request->submission_time)) $product_attribute->submission_time=$request->submission_time;
                if(isset($request->stock)) {
                    $product_attribute->initial_stock=$request->stock;
                    $product_attribute->stock=$request->stock;
                }
                if(isset($request->is_show)) $product_attribute->is_show=$request->is_show;
                $product_attribute->save();
                return ['status'=>'true','stock'=>$product_attribute->stock
                    ,'submission_time'=>$product_attribute->submission_time
                    ,'price'=>$product_attribute->price,'is_show'=>$product_attribute -> is_show];
            }
        }
        return ['status'=>'false'];
    }

    //return several product_attribute fields
    public function returnProductAttribute(Request $request)
    {
        if(isset($request->product_attribute_id)){
            $product_attribute = ProductAttribute::find($request->product_attribute_id);
            return ['status'=>'true','stock'=>$product_attribute->stock
                ,'submission_time'=>$product_attribute->submission_time
                ,'price'=>$product_attribute->price
            ];
        }
        return ['status'=>'false'];
    }



}
