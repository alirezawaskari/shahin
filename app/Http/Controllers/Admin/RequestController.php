<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\MediaLibraryHelper;
use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Card;
use App\Models\ChangeStoreRoom;
use App\Models\CountingUnit;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\ProductStoreRoom;
use App\Models\StoreRoom;
use App\Models\User;
use Illuminate\Http\Request;

class RequestController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = User::find(\Auth::user()->id);
        $request_lists = [];
        if (\Auth::user()->isAbleTo('store-room')) {
            $store_rooms = StoreRoom::where('warehouse_keeper_id', $user->id)->orderBy('id', 'DESC')->get();
            $store_room_id = StoreRoom::where('warehouse_keeper_id', $user->id)->orderBy('id', 'DESC')->pluck('id');
            $request_lists = \App\Models\Request::whereIn('store_room_id', $store_room_id->toArray())->orWhere('user_id', \Auth::user()->id)->orderBy('id', 'DESC');
        }

        if (\Auth::user()->isAbleTo('store-room-detail')) {
            if ($user->admin_representation_id != null) {
                $store_rooms = StoreRoom::where('warehouse_keeper_id', $user->admin_representation_id)->get();
                $store_room_id = StoreRoom::where('warehouse_keeper_id', $user->admin_representation_id)->pluck('id');
                $request_lists = \App\Models\Request::whereIn('store_room_id', $store_room_id->toArray())->orderBy('id', 'DESC');


            } else {
                $store_rooms = StoreRoom::orderBy('id', 'DESC')->get();
                $request_lists = \App\Models\Request::orderBy('id', 'DESC');
            }
        }

        $cards = Card::all();
        $queries = [];
        $columns = ['name', 'family', 'status', 'card_id', 'store_room_id'];
        foreach ($columns as $column) {
            if ($request->has($column)) {
                if ($column === 'name' && $request->$column != '') {
                    $request_lists->with('user')
                        ->whereHas('user', function ($query) use ($request, $column) {
                            $query->where('name', 'like', '%' . $request->$column . '%');
                        });
                    $queries[$column] = $request->$column;
                }

                if ($column === 'family' && $request->$column != '') {
                    $request_lists->with('user')
                        ->whereHas('user', function ($query) use ($request, $column) {
                            $query->where('family', 'like', '%' . $request->$column . '%');
                        });
                    $queries[$column] = $request->$column;
                } elseif ($request->$column != 'all' && $column != 'family' && $column != 'name') {
                    $request_lists = $request_lists->where($column, $request->$column);
                    $queries[$column] = $request->$column;
                } elseif ($request->$column == 'all') {
                    $queries[$column] = 'all';
                }
            }
        }
        if (isset($request_lists)) {
            $request_lists = $request_lists->paginate(15)->appends($queries);
            foreach ($request_lists as $q) {
                $q['card_name'] = Card::getCard($q->card_id);
                $q['store_room_name'] = StoreRoom::getStoreRoom($q->store_room_id, 'name');
            }
        }

        $statuses = ['1' => 'پیش نویس', '2' => 'در انتظار تایید انباردار', '3' => 'در انتظار ارسال',
            '4' => 'رد شده', '5' => 'ارسال شده', '6' => 'دریافت شد'];

        if ($request->ajax()) {
            return view('Admin.request.load', ['request_lists' => $request_lists])->render();
        }
        return view('Admin.request.lists', compact('request_lists', 'statuses', 'queries',
            'cards', 'store_rooms'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $store_rooms = StoreRoom::orderBy('id', 'DESC')->get();
        return view('Admin.request.create', compact('store_rooms'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $user = User::find(\Auth::user()->id);
        $request['user_id'] = \Auth::user()->id;
        if (isset($user->roles) && isset($user->roles()->pluck('card_id')[0])) {
            $request['card_id'] = $user->roles()->pluck('card_id')[0];
        }
        // legacy media columns are never mass-assigned — see AdoptsLegacyMedia::exceptLegacyMediaKeys()
        $request_rec = \App\Models\Request::create((new \App\Models\Request)->exceptLegacyMediaKeys($request->all()));

        if ($request->file_path) {
            MediaLibraryHelper::attachTo($request_rec, $request->file_path, 'file');
        }

        if (isset($request->products_number)) {
            foreach ($request->products_number as $ind => $item) {
                $product_id = ProductStoreRoom::find($ind)->product_id ;
                $request_rec->requestItems()->attach($product_id, ['number' => $item['number']]);
            }
        }

        if (isset($request->productAttributeSelect)) {
            foreach ($request->productAttributeSelect as $product_attribute_id) {
                if(isset($request->product_attribute_ids[$product_attribute_id]['number'])) {
                    $number = $request->product_attribute_ids[$product_attribute_id]['number'];
                    $product_id = ProductAttribute::find($product_attribute_id)->product_id;
                    $request_rec->requestItems()->attach($product_id, ['number' => $number,
                        'product_attribute_id' => $product_attribute_id]);
                }
            }
        }

        return redirect('/dashboard/request')->with(['create' => 'success']);
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if ($id) {
            $request_rec = \App\Models\Request::find($id);

            foreach ($request_rec->requestItems as $item) {
                if ($item->pivot->product_attribute_id != null) {
                    $item['stock'] = \App\Models\Request::stock($request_rec->id, $item->pivot->product_id, $item->pivot->product_attribute_id);
                }
                if ($item->pivot->product_attribute_id == null) {
                    $item['stock'] = \App\Models\Request::stock($request_rec->id, $item->pivot->product_id, null);
                }
                $item['brand'] = \App\Models\Request::brandProductInRequest($item->pivot->product_id);
            }
            return view('Admin.request.show', compact('request_rec'));
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
        $request_record = \App\Models\Request::find($id);

        $products_id = $request_record->requestItems()->pluck('product_id')->toArray();

        $products_attribute_id = $request_record->requestItems()->pluck('product_attribute_id')->toArray();

        $product_store_room_ids = ProductAttribute::whereIn('id',$products_attribute_id)
            ->where('productattributeable_type',ProductStoreRoom::class)->pluck('productattributeable_id')->toArray();


        $products = $this->handleProducts($request_record->store_room_id, $request_record->type);

        $lists = [];
        if (isset($products_id) && isset($request_record->type) && isset($request_record->store_room_id)) {

            foreach ($products_id as $id) {

                $product = Product::find($id);
                $lists[$id]['internal_use'] = $product->internal_use;
                $lists[$id]['type'] = $product->type;
                if (isset($request_record->id) && $request_record->id != 0) {
                    $lists[$id]['number'] = 0;
                    $request_rec = \App\Models\Request::find($request_record->id);
                    $request_item = $request_rec->requestItems()->where('product_id', $id)->first();

                    if (isset($request_item->pivot)) $lists[$id]['number'] = $request_item->pivot->number;
                }

                //material
                if ($request_rec->type == 0 || $product->internal_use == 1) {

                    $store_rooms = $product->storeRooms()
                        ->where('store_rooms.id', $request_rec->store_room_id);

                    $product_store_rooms_stock = $store_rooms->sum('stock');
                    $product_store_rooms = $store_rooms->first();
                    $lists[$id]['name'] = $product->name;
                    $lists[$id]['stock'] = $product_store_rooms_stock;

                    $pishvand = ' در هر ';
                    $pasvand = 'وجود دارد';
                    $main_unit_name = CountingUnit::getCountingUnit($product_store_rooms->pivot->main_unit_id, 'title');
                    $sub_unit_name = CountingUnit::getCountingUnit($product_store_rooms->pivot->sub_unit_id, 'title');
                    $sub_unit_value = $product_store_rooms->pivot->sub_unit_value;
                    $lists[$id]['unit'] = ' ( ' . $pishvand . ' ' . $main_unit_name . ' ' . $sub_unit_value . ' ' . $sub_unit_name . ' ' . $pasvand . ' ) ';
                }

                //good
                if ($request_rec->type == 1 || $product->internal_use == 0) {
                    $attributes = [];
                    $product_store_rooms = $product->storeRooms()
                        ->where('store_rooms.id', $request_rec->store_room_id)->first();

                    $product_attributes = $product->attributes()->where('stock', '!=', null)
                        ->where('productattributeable_type', ProductStoreRoom::class)
                        ->whereIn('productattributeable_id', $product_store_room_ids)->get();

                    if ($product_attributes) {
                        $lists[$id]['name'] = $product->name;

                        foreach ($product_attributes as $product_attribute) {

                            $request_item = $request_rec->requestItems()->where('product_id', $id)
                                ->where('product_attribute_id', $product_attribute->pivot->id)->first();
                            if (isset($request_item->pivot)) $attributes[$product_attribute->pivot->id]['number'] = $request_item->pivot->number;

                            $attributes[$product_attribute->pivot->id]['value'] = $product_attribute->pivot->value;
                            $attributes[$product_attribute->pivot->id]['stock'] = $product_attribute->pivot->stock;
                        }
                    }
                    $lists[$id]['attributes'] = $attributes;
                }
            }
        }

        return view('Admin.request.edit', compact('products_id', 'request_record', 'products', 'lists','product_store_room_ids'));


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
        $request_rec = \App\Models\Request::find($id);

        if (isset($request->productAttributeSelect)) {
            foreach ($request_rec->requestItems as $requestItem) {
                $requestItem->pivot->delete();
            }

            foreach ($request->productAttributeSelect as $product_attribute_id) {

                $number = $request->product_attribute_ids[$product_attribute_id]['number'];

                $product_id = ProductAttribute::find($product_attribute_id)->product_id;

                $request_rec->requestItems()->attach($product_id,['number' => $number,
                    'product_attribute_id' => $product_attribute_id]);
            }
        }


        if (isset($request->products_number)) {
            foreach ($request_rec->requestItems as $requestItem) {
                $requestItem->pivot->delete();
            }
            foreach ($request->products_number as $ind => $item) {
                $request_rec->requestItems()->attach($ind, ['number' => intval($item['number'])]);
            }
        }

        return redirect('/siteAdmin/request')
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
        $rec = \App\Models\Request::findOrFail($id);
        $rec->requestItems()->detach();
        $rec->delete();
        return redirect('/dashboard/request')->with(['delete' => 'success']);
    }

    /**
     * @param Request $request
     * @return array|string[]
     */
    public function productByStoreRoom(Request $request)
    {
        if (isset($request->store_room_id) && isset($request->type)) {

            $products = $this->handleProducts($request->store_room_id, $request->type);

            return ['status' => 'true', 'products' => $products];
        }
        return ['status' => 'false', 'products' => ''];
    }

    /**
     * @param $store_room_id
     * @param $type
     * @return array|void
     */
    public function handleProducts($store_room_id, $type)
    {
        //material
        if($type == 0){

            $products = StoreRoom::find($store_room_id)->products()->where('type', $type)
                ->wherePivot('stock','>',0)
                ->get();

//            $products=Product::whereHas('storeRooms', function($query) use ($store_room_id) {
//                $query->where('store_rooms.id',$store_room_id)->where('stock','>',0);
//            })->where('type', $type)->distinct()->get();

        }

        //good
        if($type == 1){
            $products_lists=[];

            $products = StoreRoom::find($store_room_id)->products()->where('type', $type)->get();

            foreach ($products as $product) {
                if($product->internal_use == 0 ){
                    if($product->attributes()->where('price','!=',0)->sum('stock') > 0){
                        $products_lists[] = $product;
                    }
                }
                if($product->internal_use == 1 ){
                    if($product->storeRooms()->sum('stock') > 0)
                    $products_lists[] = $product;
                }
            }

            if($products_lists != []) $products = $products_lists;

        }

        if($products){
            foreach ($products as $product) {
                $product['product_store_room_id'] = $product->pivot->id;
                $product['store_room_name'] =
                    StoreRoom::find($product->pivot->store_room_id)->name .'  (  '. $product->pivot->id .':'. number_format($product->pivot->sales_price) .' )  ';

                $product['brand_title']= '';
                if(isset($product->brand_id ) && $product->brand_id != '' && isset(Brand::find($product->brand_id)->title)) {
                    $product['brand_title']= ' - برند : ' . Brand::find($product->brand_id)->title ;
                }
            }
            return $products;
        }
    }


    /**
     * @param $id
     * @param $status
     * @return \Illuminate\Http\RedirectResponse
     */
    public function changeStatusRequest($id, $status)
    {
        $request = \App\Models\Request::find($id);
        $request->status = $status;

        if ($status == 3 || $status == 4) {
            $request->confirmator = \Auth::user()->id;
        }

        if ($status == 5) {
            $request->sender_id = \Auth::user()->id;
            foreach ($request->requestItems as $requestItem) {
                $new_number = $requestItem->pivot->number;
                if ($request->type == 0) {
                    $product_store_rooms = StoreRoom::find($request->store_room_id)->products()
                        ->where('products.id', $requestItem->id)->get();
                    foreach ($product_store_rooms as $item) {

                        if ($new_number > 0 && isset($item->pivot)) {
                            $stock = $item->pivot->stock;
                            if ($stock >= $new_number) {
                                $new_stock = $stock - $new_number;
                                $item->pivot->stock = $new_stock;
                                $item->pivot->save();
                                $new_number = 0;
                            } else if ($stock < $new_number) {
                                $item->pivot->stock = 0;
                                $item->pivot->save();
                                $new_number = $new_number - $stock;
                            }
                        }
                    }

                    $this->ChangeStoreRoom($requestItem->pivot->product_id, $request->store_room_id, StoreRoom::class, $requestItem->pivot->number, 0);
                }

                if ($request->type == 1) {
                    $product = Product::find($requestItem->pivot->product_id);
                    if ($product->internal_use == 0) {
                        $product_attribute = ProductAttribute::find($requestItem->pivot->product_attribute_id);

                        $stock = $product_attribute->stock;

                        if ($stock >= $requestItem->pivot->number) {
                            $this->ChangeStoreRoom($requestItem->pivot->product_id, $requestItem->pivot->product_attribute_id, 'App\ProductAttribute', $requestItem->pivot->number, 0);

                            $new_stock = $stock - $requestItem->pivot->number;
                            $product_attribute->stock = $new_stock;
                            $product_attribute->save();
                        }
                    }
                    if ($product->internal_use == 1) {
                        $product_store_rooms = StoreRoom::find($request->store_room_id)->products()
                            ->where('products.id', $requestItem->id)->get();
                        foreach ($product_store_rooms as $item) {

                            if ($new_number > 0 && isset($item->pivot)) {
                                $stock = $item->pivot->stock;
                                if ($stock >= $new_number) {
                                    $new_stock = $stock - $new_number;
                                    $item->pivot->stock = $new_stock;
                                    $item->pivot->save();
                                    $new_number = 0;
                                } else if ($stock < $new_number) {
                                    $item->pivot->stock = 0;
                                    $item->pivot->save();
                                    $new_number = $new_number - $stock;
                                }
                            }
                        }

                        $this->ChangeStoreRoom($requestItem->pivot->product_id, $request->store_room_id, StoreRoom::class, $requestItem->pivot->number, 0);
                    }
                }
            }
        }
        $request->save();
        return redirect()->back();
    }

    /**
     * @param Request $request
     * @return string[]
     */
    public function rejectedStatusRequest(Request $request)
    {
        if (isset($request->id) && isset($request->message) && $request->id != '' && $request->message != '') {
            $request_rec = \App\Models\Request::find($request->id);
            $request_rec->description = $request->message;
            $request_rec->status = 4;
            $request_rec->save();
            return ['status' => 'true'];
        }
        return ['status' => 'false'];
    }

    /**
     * @param Request $request
     * @return array|string[]
     */
    public function stockProduct(Request $request)
    {
        $lists = [];
        if (isset($request->ids) && isset($request->type) && isset($request->store_room_id)) {

            foreach ($request->ids as $id) {
                $product_store_room = ProductStoreRoom::find($id);
                if (isset($request->request_id) && $request->request_id != 0) {
                    $lists[$id]['number'] = 0;
                    $request_rec = \App\Models\Request::find($request->request_id);
                    $request_item = $request_rec->requestItems()->where('product_id', $product_store_room->product_id)->first();

                    if (isset($request_item->pivot)) $lists[$id]['number'] = $request_item->pivot->number;
                }

                $product = Product::find($product_store_room->product_id);
                $lists[$id]['internal_use'] = $product->internal_use;
                $lists[$id]['type'] = $product->type;

                //material
                if ($request->type == 0 || $product->internal_use == 1) {

                    $store_rooms=$product->storeRooms()
                        ->where('store_rooms.id', $request->store_room_id);

                    $product_store_rooms_stock = $store_rooms->sum('stock');
                    $product_store_rooms = $store_rooms->first();
                    $lists[$id]['name'] = $product->name;
                    $lists[$id]['stock'] = $product_store_rooms_stock;

                    $pishvand = ' در هر ';
                    $pasvand = 'وجود دارد';
                    $main_unit_name=CountingUnit::getCountingUnit($product_store_rooms->pivot->main_unit_id,'title');
                    $sub_unit_name = CountingUnit::getCountingUnit($product_store_rooms->pivot->sub_unit_id,'title');
                    $sub_unit_value = $product_store_rooms->pivot->sub_unit_value;
                    $lists[$id]['unit']=' ( '. $pishvand .' '. $main_unit_name . ' ' .$sub_unit_value .' '. $sub_unit_name .' '. $pasvand .' ) ';
                }

                //good
                if ($request->type == 1 || $product->internal_use == 0) {
                    $attributes = [];

                    $product_attributes = $product->attributes()->where('stock', '!=', null)
                        ->where('productattributeable_type', ProductStoreRoom::class)
                        ->where('productattributeable_id', $id)->get();


                    if ($product_attributes) {
                        $lists[$id]['name'] = $product->name;

                        foreach ($product_attributes as $product_attribute) {
                            if(isset($request_rec)){
                                $request_item = $request_rec->requestItems()->where('product_id', $product_store_room->product_id)
                                    ->where('product_attribute_id',$product_attribute->pivot->id)->first();
                                if (isset($request_item->pivot)) $attributes[$product_attribute->pivot->id]['number'] = $request_item->pivot->number;
                            }

                            $attributes[$product_attribute->pivot->id]['value'] = $product_attribute->pivot->value;
                            $attributes[$product_attribute->pivot->id]['stock'] = $product_attribute->pivot->stock;
                        }
                    }
                    $lists[$id]['attributes'] = $attributes;
                }
            }

            $view = view('Admin.request.layouts.stockProduct', ['lists' => $lists]);
            $view_result = $view->render();
            return ['status' => 'true', 'view_result' => $view_result];

        }
        return ['status' => 'false'];
    }

    /**
     * @param $product_id
     * @param $changestoreroomable_id
     * @param $changestoreroomable_type
     * @param $number
     * @param $type
     */
    public function ChangeStoreRoom($product_id, $changestoreroomable_id, $changestoreroomable_type, $number, $type)
    {
        $has_change = ChangeStoreRoom::where('product_id', $product_id)->where('changestoreroomable_id', $changestoreroomable_id)
            ->where('changestoreroomable_type', $changestoreroomable_type)
            ->where('number', $number)->where('user_id', \Auth::user()->id)->where('type', $type)->count();
        if ($has_change == 0) {
            ChangeStoreRoom::create(['product_id' => $product_id, 'changestoreroomable_id' => $changestoreroomable_id
                , 'changestoreroomable_type' => $changestoreroomable_type, 'number' => $number, 'user_id' => \Auth::user()->id, 'type' => $type]);
        }
    }

}
