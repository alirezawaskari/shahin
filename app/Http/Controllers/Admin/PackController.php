<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\CategoryStoreRoom;
use App\Models\Center;
use App\Models\CountingUnit;
use App\Models\Gallery;
use App\Models\Pack;
use App\Models\StorageStoreRoom;
use App\Models\StoreRoom;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Http\Request;

class PackController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $center_id = null;
        if (User::checkManager() != null) {
            $center_id = User::checkManager();
            $packs = Center::find($center_id)->packs()->orderBy('id', 'DESC')->get();
        }
        else
            $packs = Pack::orderBy('id', 'DESC')->get();


        foreach ($packs as $pack) {
            $pack['products'] = $pack->products()->select('products.id','products.name')->get();
        }

        return view('Admin.packs.list', ['packs' => $packs]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $storage_store_rooms = StorageStoreRoom::all();
        $centers = Center::all();
        $category_store_rooms = CategoryStoreRoom::all();
        $store_rooms = StoreRoom::all();
        $counting_units = CountingUnit::where('type', 'packing')->get();
        $counting_units_measurement = CountingUnit::where('type', 'measurement')->get();
        $tags = Tag::all();

        $center_id = null;
        if (User::checkManager() != null) $center_id = User::checkManager();


        return view('Admin.packs.create', compact('centers', 'store_rooms',
            'category_store_rooms', 'counting_units', 'tags', 'storage_store_rooms',
            'counting_units_measurement','center_id'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $packByCenter=$request->packByCenter;

        //انبار
        if ($request->type_good_selected == 0) { $this->storeValidationStoreRoom($request); }

        //مرکز
        if ($request->type_good_selected == 1) { $this->storeValidationCenter($request,$packByCenter); }

        $request['isShow'] = isset($request->isShow) ? 1 : 0;

        $pack = Pack::create($request->all());

        //products
        $pack->products()->attach($request->product_id);

        if (isset($request->image_path)) {
            if (\File::exists(public_path($pack->image))) {
                \File::delete(public_path($pack->image));
            }
            $pack->image = Helper::moveImage($request->image_path, 'pack');
            $pack->save();
        }

        $files = $request->multiple_image;
        if ($files != '') {
            foreach ($files as $extra_file) {
                if (isset($extra_file['file'])) {
                    $image = Helper::moveImage($extra_file['file'], 'pack');
                    Gallery::create(['galleryable_id' => $pack->id,
                        'galleryable_type' => Pack::class, 'title' => $extra_file['title'], 'image' => $image]);
                }
            }
        }

        $price = 0;
        //stock
        if ($request->type_good_selected == 0) {
            if (\Auth::user()->isAbleTo('product-detail')) {
                // Product with StoreRoom
                $expire_date = Helper::convert($request->pack['expire_date']);

                $store_room_discount = $request->pack['discount'];
                if($store_room_discount > 0 )$store_room_discount = str_replace(",", "", $store_room_discount);

                $store_room_sales_price = $request->pack['sales_price'];
                if($store_room_sales_price > 0 )$store_room_sales_price = str_replace(",", "", $store_room_sales_price);

                $store_room_purchase_price = $request->pack['purchase_price'];
                if($store_room_purchase_price > 0 )$store_room_purchase_price = str_replace(",", "", $store_room_purchase_price);


                $pack->storeRooms()->attach($request->pack['store_room_id'],
                    [
                        'category_store_room_id' => $request->pack['category_store_room_id'],
                        'storage_store_room_id' => $request->pack['storage_store_room_id'],
                        'row_num' => $request->pack['row_num'],
                        'col_num' => $request->pack['col_num'],
                        'discount' => $store_room_discount,
                        'order_point' => $request->pack['order_point'],
                        'order_day' => $request->pack['order_day'],
                        'expire_alert' => $request->pack['expire_alert'],
                        'expire_date' => $expire_date,
                        'measurement_unit_id' => $request->pack['measurement_unit_id'],
                        'purchase_type' => $request->pack['purchase_type'],
                        'pay_type' => $request->pack['pay_type'],
                        'sales_price' => $store_room_sales_price,
                        'purchase_price' => $store_room_purchase_price,
                        'barcode' => $request->pack['barcode'],
                        'main_unit_id' => $request->pack['main_unit_id'],
                        'sub_unit_id' => $request->pack['sub_unit_id'],
                        'main_unit_value' => $request->pack['main_unit_value'],
                        'sub_unit_value' => $request->pack['sub_unit_value'],
                        'storage_num' => $request->pack['storage_num']
                    ]);

                $price = $store_room_sales_price;
            }
        }
        $center = Center::find($request->pack['center_id']);
        if (isset($center)) {
            $price_center = $request->pack['price_center'];
            $price_center = str_replace(",", "", $price_center);

            $pack->centers()->attach($request->pack['center_id'], ['purchase_type' => $center->purchase_type, 'pay_type' => $center->pay_type,
                'price' => $price_center, 'stock' => $request->pack['stock_center']
            ]);

            $price = $price_center;
        }

        $pack->price = $price;
        $pack->save();

        //tag
        if (isset($request->tag) && $request->tag) $pack->tag(['tag_name' => $request->tag]);


        return redirect('/dashboard/pack/create')->with(['create' => 'success']);

    }

    /**
     * @param $request
     */
    public function storeValidationStoreRoom($request)
    {
        $request->validate(
            [
                'title' => 'required', 'image' => 'required', 'product_id' => 'required',
                'pack.category_store_room_id' => 'required',
                'pack.store_room_id' => 'required',
                'pack.storage_store_room_id' => 'required',
                'pack.row_num' => 'required', 'pack.col_num' => 'required',
                'pack.order_point' => 'required',
                'pack.order_day' => 'required',
                'pack.expire_alert' => 'required',
                'pack.expire_date' => 'required',
                'pack.purchase_type' => 'required', 'pack.measurement_unit_id' => 'required',
                'pack.pay_type' => 'required',
                'pack.stock' => 'required',
                'pack.purchase_price' => 'required',
                'pack.main_unit_id' => 'required',
                'pack.sub_unit_id' => 'required',
                'pack.main_unit_value' => 'required',
                'pack.sub_unit_value' => 'required',
                'pack.storage_num' => 'required',
            ],
            [
                'title.required' => 'لطفا عنوان را وارد کنید',
                'image.required' => 'لطفا عکس را وارد کنید', 'product_id.required' => 'لطفا کالا ها خود را انتخاب کنید',
                'pack.category_store_room_id.required' => 'لطفا دسته بندی انبار را انتخاب کنید',
                'pack.store_room_id.required' => 'لطفا انبار را انتخاب کنید',
                'pack.storage_store_room_id.required' => 'لطفا محل نگهداری را انتخاب کنید',
                'pack.row_num.required' => 'لطفا شماره سطر را وارد کنید',
                'pack.col_num.required' => 'لطفا شماره ستون را وارد کنید',
                'pack.order_point.required' => 'لطفا نقطه سفارش را وارد کنید',
                'pack.order_day.required' => 'لطفا مدت زمان سفارش را وارد کنید',
                'pack.expire_alert.required' => 'لطفا اعلام انقضا را وارد کنید',
                'pack.expire_date.required' => 'لطفا تاریخ انقضا را وارد کنید',
                'pack.measurement_unit_id.required' => 'لطفا واحد اندازه گیری مصرفی را انتخاب کنید',
                'pack.purchase_type.required' => 'لطفا نوع خرید را انتخاب کنید',
                'pack.pay_type.required' => 'لطفا نوع پرداخت را انتخاب کنید',
                'pack.stock.required' => 'لطفا موجودی را انتخاب کنید',
                'pack.purchase_price.required' => 'لطفا قیمت خرید را وارد کنید',
                'pack.main_unit_id.required' => 'لطفا واحد اصلی را انتخاب کنید',
                'pack.main_unit_value.required' => 'لطفا  تعداد واحد اصلی را وارد کنید',
                'pack.sub_unit_id.required' => 'لطفا واحد فرعی را انتخاب کنید',
                'pack.sub_unit_value.required' => 'لطفا تعداد واحد فرعی را وارد کنید',
                'pack.storage_num.required' => 'لطفا شماره محل نگهداری را وارد کنید',
            ]
        );
    }

    /**
     * @param $request
     * @param $packByCenter
     */
    public function storeValidationCenter($request, $packByCenter)
    {
        //center by manager center
        if($packByCenter == true) {
            $request->validate([
                    'title' => 'required',
                    'image' => 'required',
                    'pack.price_center' => 'required',
                    'pack.stock_center' => 'required',
                ],
                [
                        'title.required' => 'لطفا عنوان را وارد کنید',
                        'image.required' => 'لطفا عکس را وارد کنید',
                        'product_id.required' => 'لطفا کالا ها خود را انتخاب کنید',
                        'pack.price_center.required' => 'لطفا قیمت را وارد کنید',
                        'pack.stock_center.required' => 'لطفا موجودی را وارد کنید',
                    ]
            );
        }

        if($packByCenter == false) {
            $request->validate([
                'title' => 'required',
                'image' => 'required',
                'pack.price_center' => 'required',
                'pack.stock_center' => 'required',
                'pack.center_id' => 'required',
            ],
                [
                    'title.required' => 'لطفا عنوان را وارد کنید',
                    'image.required' => 'لطفا عکس را وارد کنید',
                    'pack.price_center.required' => 'لطفا قیمت را وارد کنید',
                    'pack.stock_center.required' => 'لطفا موجودی را وارد کنید',
                    'pack.center_id.required' => 'لطفا مرکز را انتخاب کنید',
                ]
            );
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $storage_store_rooms = StorageStoreRoom::all();
        $centers = Center::all();
        $category_store_rooms = CategoryStoreRoom::all();
        $store_rooms = StoreRoom::all();
        $counting_units = CountingUnit::where('type', 'packing')->get();
        $counting_units_measurement = CountingUnit::where('type', 'measurement')->get();
        $tags = Tag::pluck('name');
        $pack = Pack::find($id);

        $pack_centers = $pack->centers()->first();
        $pack_store_rooms = Pack::find($id)->storeRooms()->first();

        if($pack_centers != [])
            $products = Center::find($pack_centers->id)->products()->where('type', 1)->orderBy('id', 'DESC')->get();

        if($pack_store_rooms != [])
            $products = StoreRoom::find($pack_store_rooms->id)->products()->where('type', 0)->orderBy('id', 'DESC')->get();

        $products = $products->unique();

        $center_id = null;
        if (User::checkManager() != null) $center_id = User::checkManager();

        return view('Admin.packs.edit', compact('centers', 'store_rooms',
            'category_store_rooms', 'counting_units', 'tags', 'pack', 'products', 'pack_centers', 'pack_store_rooms',
            'storage_store_rooms', 'counting_units_measurement','center_id'));
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
        $pack = Pack::find($id);
        $pack_by_center = $request->packByCenter;
        if ($id && isset($pack)) {
            $request['isShow'] = isset($request['isShow']) ? 1 : 0;

            //انبار
            $has_store_rooms = $pack->storeRooms;
            if (isset($has_store_rooms) && count($has_store_rooms) > 0) {
                $this->updateValidationStoreRoom($request);
            }

            //مرکز
            $pack_center = $pack->centers;
            if (isset($pack_center) && count($pack_center) > 0) {
                $this->updateValidationCenter($request,$pack_by_center);
            }

            $pack->update($request->all());

            //products
            if ($pack->products()->count() > 0)
                $pack->products()->sync($request->product_id);
            else
                $pack->products()->attach($request->product_id);

            //tag
//            $list_tags_pack = $pack->tagNames();
//            if ($list_tags_pack != []) {
//                $request_tags = $request->tag;
//                if ($list_tags_pack && $request_tags) {
//                    $removing_tag = array_diff($list_tags_pack, $request_tags);
//                    $new_tags = array_diff($request_tags, $list_tags_pack);
//                    $pack->untag($removing_tag);
//                    $pack->tag($new_tags);
//                }
//            } else {
//                if (isset($request->tag) && $request->tag)
//                    $pack->tag(['tag_name' => $request->tag]);
//            }

            //images
            if (isset($request->image_path)) {
                if (\File::exists(public_path($pack->image))) {
                    \File::delete(public_path($pack->image));
                }
                $pack->image = Helper::moveImage($request->image_path, 'pack');
                $pack->save();
            }

            $files = $request->multiple_image;
            if ($files != '') {
                foreach ($files as $extra_file) {
                    if (isset($extra_file['file'])) {
                        $image = Helper::moveImage($extra_file['file'], 'pack');
                        Gallery::create(['galleryable_id' => $pack->id,
                            'galleryable_type' => Pack::class , 'title' => $extra_file['title'], 'image' => $image]);
                    }
                }
            }


            $price = 0;
            // pack with StoreRoom
            if (isset($request->store_room_id) && $request->store_room_id != '') {
                $store_room = $pack->storeRooms()->where('pack_store_room.store_room_id', $request->store_room_id)->first();
                if ($store_room && isset($store_room->pivot)) {
                    $store_room->pivot->update(
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
                            'sales_price' => $request->sales_price,
                            'purchase_price' => $request->purchase_price,
                            'barcode' => $request->barcode,
                            'main_unit_id' => $request->main_unit_id,
                            'sub_unit_id' => $request->sub_unit_id,
                            'main_unit_value' => $request->main_unit_value,
                            'sub_unit_value' => $request->sub_unit_value,
                            'storage_num' => $request->storage_num
                        ]);
                    $price = $request->sales_price;
                }
            }

            if (isset($pack_center) && count($pack_center) > 0) {
                foreach ($pack_center as $pack_c){
                    $pack_c->pivot->center_id =$request->center_id;
                    $pack_c->pivot->price = $request->price;
                    $pack_c->pivot->stock = $request->stock;
                    $pack_c->pivot->save();
                    $price = $request->price;
                }
            }

            $pack->price = $price;
            $pack->save();

            return redirect('/dashboard/pack')->with(['update' => 'success']);

        }
        return redirect("/dashboard/pack");
    }

    /**
     * @param $request
     * @param $pack_by_center
     */
    public function updateValidationCenter($request, $pack_by_center)
    {
        if ($pack_by_center == 1) {
            $request->validate([
                'product_id' => 'required',
                'price' => 'required',
                'stock' => 'required',
            ],
                [
                    'price.required' => 'لطفا قیمت را انتخاب کنید',
                    'stock.required' => 'لطفا  موجودی را انتخاب کنید',
                    'product_id.required' => 'لطفا کالا ها را انتخاب کنید'
                ]
            );

        }

        if (\Auth::user()->isAbleTo('product-detail')) {
            $request->validate([
                'product_id' => 'required',
                'price' => 'required',
                'stock' => 'required',
            ],
                [
                    'product_id.required' => 'لطفا کالا را انتخاب کنید',
                    'price.required' => 'لطفا قیمت را انتخاب کنید',
                    'stock.required' => 'لطفا موجودی را انتخاب کنید'
                ]
            );
        }
    }


    /**
     * @param $request
     */
    public function updateValidationStoreRoom($request)
    {
        $request->validate(
            [
                'title' => 'required', 'product_id' => 'required',
                'category_store_room_id' => 'required',
                'store_room_id' => 'required',
                'storage_store_room_id' => 'required',
                'row_num' => 'required', 'col_num' => 'required',
                'discount' => 'required', 'order_point' => 'required',
                'order_day' => 'required',
                'expire_alert' => 'required',
                'expire_date' => 'required',
                'purchase_type' => 'required', 'measurement_unit_id' => 'required',
                'pay_type' => 'required',
                'purchase_price' => 'required',
                'main_unit_id' => 'required',
                'sub_unit_id' => 'required',
                'main_unit_value' => 'required',
                'sub_unit_value' => 'required',
                'storage_num' => 'required',
            ],
            [
                'title.required' => 'لطفا عنوان را وارد کنید',
                'image.required' => 'لطفا عکس را وارد کنید', 'product_id.required' => 'لطفا کالا ها خود را انتخاب کنید',
                'category_store_room_id.required' => 'لطفا دسته بندی انبار را انتخاب کنید',
                'store_room_id.required' => 'لطفا انبار را انتخاب کنید',
                'storage_store_room_id.required' => 'لطفا محل نگهداری را انتخاب کنید',
                'row_num.required' => 'لطفا شماره سطر را وارد کنید',
                'col_num.required' => 'لطفا شماره ستون را وارد کنید',
                'discount.required' => 'لطفا تخفیف را وارد کنید',
                'order_point.required' => 'لطفا نقطه سفارش را وارد کنید',
                'order_day.required' => 'لطفا مدت زمان سفارش را وارد کنید',
                'expire_alert.required' => 'لطفا اعلام انقضا را وارد کنید',
                'expire_date.required' => 'لطفا تاریخ انقضا را وارد کنید',
                'measurement_unit_id.required' => 'لطفا واحد اندازه گیری مصرفی را انتخاب کنید',
                'purchase_type.required' => 'لطفا نوع خرید را انتخاب کنید',
                'pay_type.required' => 'لطفا نوع پرداخت را انتخاب کنید',
                'stock.required' => 'لطفا موجودی را انتخاب کنید',
                'purchase_price.required' => 'لطفا قیمت خرید را وارد کنید',
                'main_unit_id.required' => 'لطفا واحد اصلی را انتخاب کنید',
                'main_unit_value.required' => 'لطفا  تعداد واحد اصلی را وارد کنید',
                'sub_unit_id.required' => 'لطفا واحد فرعی را انتخاب کنید',
                'sub_unit_value.required' => 'لطفا تعداد واحد فرعی را وارد کنید',
                'storage_num.required' => 'لطفا شماره محل نگهداری را وارد کنید',
            ]
        );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if ($id) {
            $pack = Pack::find($id);
            if (isset($pack->storeRooms) && count($pack->storeRooms) > 0) {
                $pack->storeRooms()->detach();
            }
            if (isset($pack->centers) && count($pack->centers) > 0) {
                $pack->centers()->detach();
            }
            if (isset($pack->storeRooms) && count($pack->storeRooms) > 0) {
                $pack->storeRooms()->detach();
            }

            if ($pack->galleries()) {
                foreach ($pack->galleries()->get() as $item) {
                    if (\File::exists(public_path($item->image))) {
                        \File::delete(public_path($item->image));
                    }
                }
            }

            if (\File::exists(public_path($pack->image))) {
                \File::delete(public_path($pack->image));
            }

            $pack->delete();
            return redirect('/dashboard/pack')->with(['delete' => 'success']);
        }
        return redirect()->back();
    }


    /**
     * @param Request $request
     * @return array|string[]
     */
    public function getProductsPack(Request $request)
    {
        if (isset($request->type) && $request->type =='center') {
            $products = [];
            $centers = Center::with('products')->whereHas('products', function ($query) {
                $query->where('isShow', 1)->where('type', 1)
                    ->select('products.id', 'products.name', 'products.isShow', 'products.type');
            })->where('id', $request->ids)->get();

            foreach ($centers as $item) {
                $products = $item->products;
            }

            return ['status' => 'true', 'products' => $products];
        }

        if (isset($request->type) && $request->type =='store_room') {
            $products = [];
            $centers = StoreRoom::with('products')->whereHas('products', function ($query) {
                $query->where('type', 1)->select('products.id', 'products.name', 'products.isShow', 'products.type');
            })->where('id', $request->ids)->get();

            foreach ($centers as $item) {
                $products = $item->products;
            }

            return ['status' => 'true', 'products' => $products];
        }

        return ['status' => 'false'];
    }
}
