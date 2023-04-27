<?php

namespace App\Http\Controllers\Admin;

use App\Exports\ProductStoreRoomExport;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\AttributeGroup;
use App\Models\Brand;
use App\Models\Category;
use App\Models\CategoryStoreRoom;
use App\Models\CountingUnit;
use App\Models\Product;
use App\Models\ProductStoreRoom;
use App\Models\Representation;
use App\Models\Review;
use App\Models\Role;
use App\Models\StorageStoreRoom;
use App\Models\StoreRoom;
use App\Models\Tag;
use App\Models\User;
use App\Models\Validation;
use Carbon\Carbon;
use Couchbase\DesignDocument;
use Cviebrock\EloquentSluggable\Services\SlugService;
use Hekmatinasser\Verta\Facades\Verta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use phpDocumentor\Reflection\DocBlock\Tags\Author;

class StoreRoomController extends Controller
{
    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function index()
    {
        $user = User::find(\Auth::user()->id);
        $store_rooms=[];
        if (\Auth::user()->isAbleTo('store-room')) {
            $store_rooms = StoreRoom::where('warehouse_keeper_id',$user->id)->orderBy('id', 'DESC')->paginate(15);
        }
        if (\Auth::user()->isAbleTo('store-room-detail')) {

            if($user->admin_representation_id != null){
                $store_rooms = StoreRoom::where('warehouse_keeper_id',$user->admin_representation_id)->orderBy('id', 'DESC')->paginate(15);
            }
            else
                $store_rooms = StoreRoom::orderBy('id', 'DESC')->paginate(15);
        }


        return view('Admin.storeRoom.lists',compact('store_rooms'));
    }

    //Subset of a warehouse

    /**
     * @param $id
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function subWarehouses($id)
    {
        $parent=StoreRoom::find($id);
        if(isset($parent))$parent_name=$parent->name;
        $store_rooms = StoreRoom::orderBy('id', 'DESC')->where('parent_id',$id)->paginate(15);
        return view('Admin.storeRoom.subWarehouses',compact('store_rooms','parent_name'));
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function create()
    {
        $representations = Representation::whereStatus(true)->get();
        $store_rooms = StoreRoom::orderBy('id', 'DESC')->get();

        $store_room_role=Role::where('name','warehouse-keeper')->first();
        $users=User::with('roles')->whereHas('roles', function ($query) use ($store_room_role)  {
            $query->where('roles.id',$store_room_role->id);
        })->get();

        return view('Admin.storeRoom.create', compact('representations','store_rooms','users'));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function store(Request $request)
    {
        $request->validate(['name' => 'required','address'=> 'required'],
            [
                'name.required' => 'لطفا نام انبار را وارد کنید',
                'address.required' => 'لطفا آدرس انبار را وارد کنید',
            ]
        );

        StoreRoom::create($request->all());
        //cache
        Cache::flush();
        return redirect(route('storeRoom.index'))->with([
            'menu_create'=>'success'
        ]);
    }

    /**
     * @param StoreRoom $storeRoom
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application|\Illuminate\Http\RedirectResponse
     */
    public function edit(StoreRoom $storeRoom)
    {
        if ($storeRoom) {
            $store_room_role=Role::where('name','warehouse-keeper')->first();
            $users=User::with('roles')->whereHas('roles', function ($query) use ($store_room_role)  {
                $query->where('roles.id',$store_room_role->id);
            })->get();
            $store_rooms = StoreRoom::orderBy('id', 'DESC')->get();
            $representations = Representation::whereStatus(true)->get();
            return view('Admin.storeRoom.edit', compact('storeRoom','representations','users','store_rooms'));
        }
        return redirect()->back();
    }

    /**
     * @param Request $request
     * @param StoreRoom $storeRoom
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request, StoreRoom $storeRoom)
    {
        $this->validate(request(), [
            'address' => 'required',
        ]);

        $inputs = $request->all();
        $storeRoom->update($inputs);

        //cache
        Cache::flush();

        return redirect(route('storeRoom.index'))->with([
            'menu_update' => 'success'
        ]);
    }

    //Products of each warehouse and products by internal_use true

    /**
     * @param $id
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application|\Illuminate\Http\Response|string|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function storeRoomGood($id, Request $request)
    {
        $storeRoom=StoreRoom::find($id);
        $category_store_rooms = CategoryStoreRoom::all();
        $products_store_room = $storeRoom->products()->where(function ($query) {
            $query->where('type', 1)->orWhere('internal_use', '1');
        })->orderBy('id','DESC');

        $name = request('name');
        $startDate = request('start_date');
        $endDate = request('end_date');
        $storage = request('storage');
        $main_unit = request('main_unit');
        $category_store_room_id = request('category_store_room_id');
        $expired_products = request('expired_products');
        $expiring_products = request('expiring_products');
        $finishing_products = request('finishing_products');

        if(isset($name)){ $products_store_room=$products_store_room->where('name','like','%'. $name .'%');}
        if(isset($main_unit) && trim($main_unit) != '' && $main_unit != 'all'){$products_store_room=$products_store_room->where('main_unit_id', $main_unit);}
        if(isset($storage) && trim($storage) != '' && $storage != 'all')
        {$products_store_room=$products_store_room->where('storage_store_room_id', $storage);}

        if(isset($category_store_room_id) && trim($category_store_room_id) != '' && $category_store_room_id != 'all')
        {$products_store_room=$products_store_room->where('category_store_room_id', $category_store_room_id);}

        if (isset($startDate) && trim($startDate) != '' && isset($endDate) && trim($endDate) != '') {
            $products_store_room=$products_store_room->whereDate('created_at', '>=', $startDate)
                ->whereDate('created_at', '<=', $endDate);
        }

        if ($expired_products == 1) {
            $products_store_room->where('expire_date' , '<=' , Carbon::now());
        }

        if ($expiring_products == 1) {
            $products_store_room->where('expire_date' , '<' , Carbon::now()->addDays('expire_alert'))->where('expire_date' , '<' , Carbon::now());
        }

        if ($finishing_products == 1) {
            $products_store_room->whereRaw("order_point >=  stock");
        }

        if (\request('exel') != null){
            return (new ProductStoreRoomExport($products_store_room))->download('store_room_products.xlsx');
        }

        $products_store_room = $products_store_room->paginate(15);

        foreach ($products_store_room as $product) {
            if(isset($product->pivot)){
                $product['category_store_room_title'] = CategoryStoreRoom::getCategoryStoreRoom($product->pivot->category_store_room_id,'title');
                $product['storage_store_room_title'] = StorageStoreRoom::getStorageStoreRoom($product->pivot->storage_store_room_id,'title');
                $product['main_unit_title'] = CountingUnit::getCountingUnit($product->pivot->main_unit_id,'title');
                $product['store_room_name'] = StoreRoom::find($product->pivot->store_room_id)->name;
                $product['brand_title'] = Brand::getBrand($product->brand_id,'title');
                if($product->pivot->user_id != null)
                $product['user_id'] = User::find($product->pivot->user_id)->name . ' ' . User::find($product->pivot->user_id)->family;
                else
                $product['user_id']='';
            }
        }

        $create_link_material=route('createMaterialsByValues',['type'=>'store_room_internal_use','id'=>$id]);
        $create_link=route('createGoodByValues',['type'=>'store_room','id'=>$id]);

        $main_units = CountingUnit::where('type' , 'packing')->get();
        $page_link = route('storeRoomGood',$id) ;

        $store_room_title='';
        $store_room=StoreRoom::find($id);
        if(isset($store_room)) $store_room_title=$store_room->name;


        if ($request->ajax()) {
            return view('Admin.productStoreRoom.product.load', ['products' => $products_store_room])->render();
        }

        return view('Admin.productStoreRoom.product.list', ['products' => $products_store_room ,'store_room'=>$store_room
            ,'create_link'=>$create_link,'store_room_title'=>$store_room_title,'store_room_id'=>$id,'create_link_material'=>$create_link_material
            ,'main_units'=>$main_units,'page_link'=>$page_link,'category_store_rooms'=>$category_store_rooms]);
    }

    /**
     * @param $id
     * @param $product_id
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function editStoreRoomGood($id, $product_id)
    {
        $product = Product::find($product_id);
        $center_id=null;
        $product_store_room = ProductStoreRoom::find($id);
        $reviews_strength = $product->reviews()->where('reviewable_type', StoreRoom::class)
            ->where('reviewable_id', $product_store_room->store_room_id)->where('type', 0)->get();
        $reviews_weakness = $product->reviews()->where('reviewable_type', StoreRoom::class)
            ->where('reviewable_id', $product_store_room->store_room_id)->where('type', 1)->get();
        $categories_product = $product->categories()->orderBy('category_product.id', 'ASC')->get();

        $parents = Category::where('parent_id', 1)->get();
        $tags = Tag::pluck('name');

        $last_category_id = Category::latestCategory($product);

        $attributeGroups = AttributeGroup::where('category_id', $last_category_id)->get();
        $attributes = [];
        if (isset($attributeGroups) && count($attributeGroups) > 0) {
            foreach ($attributeGroups as $item) {
                $attributes[$item->id] = $item->attributes()->where('is_price', 0)->get();
            }
        }
        $product_attributes = $product->attributes()
            ->where('productattributeable_id',$id )->where('productattributeable_type', StoreRoom::class)
            ->where('is_price', 0)->get();

        $brands = Category::find($last_category_id)->brands;
        $product_validation = $product->productValidations;
        $validations = Validation::all();

        $category_store_rooms = CategoryStoreRoom::all();
        $storage_store_rooms = StorageStoreRoom::all();
        $store_rooms = StoreRoom::all();
        $counting_units = CountingUnit::where('type','packing')->get();
        $counting_units_measurement = CountingUnit::where('type','measurement')->get();


        return view('Admin.productStoreRoom.product.edit', compact('reviews_strength',
            'reviews_weakness', 'categories_product', 'parents', 'tags', 'product', 'center_id',
            'product_attributes', 'brands', 'product_validation', 'validations', 'attributes'
            ,'category_store_rooms','storage_store_rooms','store_rooms','counting_units','counting_units_measurement'));
    }

    //show material product with product_store_room for list

    /**
     * @param $id
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application|\Illuminate\Http\Response|string|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function storeRoomMaterial($id, Request $request)
    {
        $storeRoom=StoreRoom::find($id);
        $products_store_room=$storeRoom->products()
            ->where(function ($query) {
                $query->where('type', 0)->where('internal_use', '0');
            })->orderBy('id','DESC');

        $name = request('name');
        $startDate = request('start_date');
        $endDate = request('end_date');
        $storage = request('storage');
        $main_unit = request('main_unit');
        $category_store_room_id = request('category_store_room_id');
        $expired_products = request('expired_products');
        $expiring_products = request('expiring_products');
        $finishing_products = request('finishing_products');

        if(isset($name)){ $products_store_room=$products_store_room->where('name','like','%'. $name .'%');}

        if(isset($main_unit) && trim($main_unit) != '' && $main_unit != 'all'){$products_store_room=$products_store_room->where('main_unit_id', $main_unit);}
        if(isset($storage) && trim($storage) != '' && $storage != 'all')
        {$products_store_room=$products_store_room->where('storage_store_room_id', $storage);}

        if(isset($category_store_room_id) && trim($category_store_room_id) != '' && $category_store_room_id != 'all')
        {$products_store_room=$products_store_room->where('category_store_room_id', $category_store_room_id);}

        if (isset($startDate) && trim($startDate) != '' && isset($endDate) && trim($endDate) != '') {
            $products_store_room=$products_store_room->whereDate('created_at', '>=', $startDate)
                ->whereDate('created_at', '<=', $endDate);
        }

        if ($expired_products == 1) {
            $products_store_room->where('expire_date' , '<=' , Carbon::now());
        }

        if ($expiring_products == 1) {
            $products_store_room->where('expire_date' , '<' , Carbon::now()->addDays('expire_alert'))->where('expire_date' , '<' , Carbon::now());
        }

        if ($finishing_products == 1) {
            $products_store_room->whereRaw("order_point >=  stock");
        }

        if (\request('exel') != null){
            return (new ProductStoreRoomExport($products_store_room))->download('store_room_products.xlsx');
        }

        $products_store_room = $products_store_room->paginate(15);

        foreach ($products_store_room as $product) {
            if(isset($product->pivot)){
                $product['category_store_room_title'] = CategoryStoreRoom::getCategoryStoreRoom($product->pivot->category_store_room_id,'title');
                $product['storage_store_room_title'] = StorageStoreRoom::getStorageStoreRoom($product->pivot->storage_store_room_id,'title');
                $product['main_unit_title'] = CountingUnit::getCountingUnit($product->pivot->main_unit_id,'title');
                $product['store_room_name'] = StoreRoom::find($product->pivot->store_room_id)->name;
                $product['stock'] = $product->pivot->stock;
                $product['brand_title'] = Brand::getBrand($product->brand_id,'title');
                if($product->pivot->user_id != null)
                $product['user_id'] = User::find($product->pivot->user_id)->name . ' ' . User::find($product->pivot->user_id)->family;
                else
                $product['user_id'] = '';

            }
        }

        $create_link = route('createMaterialsByValues',['type'=>'store_room','id'=>$id]);
        $page_link = route('storeRoomMaterial',['id'=>$id]);
        $main_units = CountingUnit::where('type' , 'packing')->get();
        $store_room_title='';
        $store_room=StoreRoom::find($id);
        if(isset($store_room)) $store_room_title=$store_room->name;
        $category_store_rooms = CategoryStoreRoom::all();

        if ($request->ajax()) {
            return view('Admin.productStoreRoom.material.load', ['products_store_room' => $products_store_room])->render();
        }
        return view('Admin.productStoreRoom.material.list', ['products_store_room' => $products_store_room
            ,'create_link'=>$create_link,'page_link'=>$page_link,'main_units'=>$main_units,'store_room_id'=>$id
            ,'store_room_title'=>$store_room_title,'category_store_rooms'=>$category_store_rooms]);
    }

    //show material product with product_store_room for edit

    /**
     * @param $id
     * @param $product_id
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application|\Illuminate\Http\RedirectResponse
     */
    public function editStoreRoomMaterial($id, $product_id)
    {
        if ($id) {
            $product = Product::find($product_id);
            $pro_store_rooms = $product->storeRooms()->wherePivot('id',$id)->first();
            $brand = Brand::where('id',$product->brand_id)->first();
            $product_store_rooms_id=$id;

            $category_store_rooms = CategoryStoreRoom::all();
            $storage_store_rooms = StorageStoreRoom::all();
            $store_rooms = StoreRoom::all();
            $counting_units = CountingUnit::where('type','packing')->get();
            $counting_units_measurement = CountingUnit::where('type','measurement')->get();
            return view('Admin.productStoreRoom.material.edit', compact('product','brand',
                'category_store_rooms','storage_store_rooms','pro_store_rooms','store_rooms','counting_units',
                'counting_units_measurement','product_store_rooms_id'));
        }
        return redirect()->back();
    }

    //update product good/material and product_store_room

    /**
     * @param Request $request
     * @param $id
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function productStoreRoomUpdate(Request $request, $id)
    {
        $request['reference_price']=str_replace(',','',$request->reference_price);
        $request['internal_use'] = isset($request['internal_use']) ? $request['internal_use'] : 0;
        $product = Product::find($id);

//  محصول یا مواد اولیه باشد و یا کالا با استفاده داخلی
        if ($product->type == 0 || $product->internal_use == 1) {

            if(isset($request->name) && $request->name != null)
                $request['slug'] = SlugService::createSlug(Product::class, 'slug', $request->name);

            if(isset($product->brand_id) && $product->brand_id != null){
                $brand = Brand::find($product->brand_id);
                if(isset($brand))
                $brand->update(['title'=>$request->brand_name_fa,'title_en'=>$request->brand_name_en]);
                else{
                    if($request->brand_name_fa != null || $request->brand_name_en != null){
                        $brand = Brand::create(['title'=>$request->brand_name_fa,'title_en'=>$request->brand_name_en]);
                        $product->brand_id = $brand->id ;
                        $product->save();
                    }
                }
            }
            else{
                if($request->brand_name_fa != null || $request->brand_name_en != null){
                    $brand = Brand::create(['title'=>$request->brand_name_fa,'title_en'=>$request->brand_name_en]);
                    $product->brand_id = $brand->id ;
                    $product->save();
                }
            }

            if(isset($request['internal_use'])){
                $internal_use = $request['internal_use'];
            }else{
                $internal_use = $product->internal_use ;
            }

            $product->update(['name' => $request->name, 'slug' => $request['slug'], 'type' => 0,
                'internal_use' => $internal_use ]);


            $store_room = $product->storeRooms()->wherePivot('id',$request->product_store_rooms_id)->first();

            $store_room_discount = $request->discount;
            if ($store_room_discount > 0) $store_room_discount = str_replace(",", "", $store_room_discount);

            $store_room_sales_price = $request->sales_price;
            if ($store_room_sales_price > 0) $store_room_sales_price = str_replace(",", "", $store_room_sales_price);

            $store_room_purchase_price = $request->purchase_price;
            if ($store_room_purchase_price > 0) $store_room_purchase_price = str_replace(",", "", $store_room_purchase_price);

            if(isset($request->stock))  $stock = $request->stock ;  else $stock = null;

            if (isset($store_room) && $store_room != '') {
                $expire_date = Helper::convert($request->expire_date);
                $expire_date = Verta::parse($expire_date);
                $expire_date = $expire_date->formatGregorian('Y-m-d H:i:s');

                dd($expire_date);

                $store_room->pivot->update(
                    [
                        'category_store_room_id' => $request->category_store_room_id,
                        'storage_store_room_id' => $request->storage_store_room_id,
                        'row_num' => $request->row_num,
                        'col_num' => $request->col_num,
                        'discount' => $store_room_discount,
                        'order_point' => $request->order_point,
                        'order_day' => $request->order_day,
                        'expire_alert' => $request->expire_alert,
                        'expire_date' => $expire_date,
                        'measurement_unit_id' => $request->measurement_unit_id,
                        'purchase_type' => isset($request->purchase_type) ? $request->purchase_type : null ,
                        'pay_type' => isset($request->pay_type) ? $request->pay_type : null ,
                        'stock' => $stock,
                        'sales_price' => $store_room_sales_price,
                        'purchase_price' => $store_room_purchase_price,
                        'barcode' => $request->barcode,
                        'main_unit_id' => $request->main_unit_id,
                        'sub_unit_id' => $request->sub_unit_id,
                        'main_unit_value' => $request->main_unit_value,
                        'sub_unit_value' => $request->sub_unit_value,
                        'storage_num' => $request->storage_num
                    ]);
            }
            if($product->internal_use  == 1){//  محصول یا مواد اولیه باشد و یا کالا با استفاده داخلی
                $product->type=1;$product->save();
                return redirect("/dashboard/storeRoomGood/$request->store_room_id")->with(['update' => 'success']);
            }
            else{
                return redirect('/dashboard/storeRoomMaterial/'.$request->store_room_id)->with(['update' => 'success']);
            }

        }


        if ($product->type == 1 && $product->internal_use == 0) {
            $request->validate(['good.name' => 'required'], ['good.name.required' => 'لطفا عنوان را وارد کنید']);

            //user is not store manager
            if(User::checkManager() == null){
                $request['reference_price'] = str_replace(',', '', $request->reference_price);
                $request['isShow'] = isset($request['isShow']) ? $request['isShow'] : 0;
                $request['is_sale'] = isset($request['is_sale']) ? $request['is_sale'] : 0;
                $request['internal_use'] = isset($request['internal_use']) ? $request['internal_use'] : 0;

                //admin:The edited product goes into product-edit review mode
                if($product->status == 4) $request['status'] = 6 ;
            }

            if(User::checkManager() != null){
                //manager : The product is confirmed and edited
                if($product->status == 5) $request['status'] = 4 ;
            }

            $productattributeable_id = 0;
            $productattributeable_type = '';

            //store_room
            if (isset($request->store_room_id)) {
                $productattributeable_id = $request->store_room_id;
                $productattributeable_type = StoreRoom::class;
            }

            Product::saveImageGood($request, $product);
            Product::updateAttributeGood($request, $product, $productattributeable_id, $productattributeable_type);

            if($request->good['name'] != null)
                $request['slug'] = SlugService::createSlug(Product::class, 'slug', $request->good['name']);

            $product = Product::find($id);

            //category
            if(isset($request->choose_category_id)){
                $all_father=Category::findAllFather($request->choose_category_id);
                array_push($all_father, $request->choose_category_id);
                $request->category_id = $all_father ;
                $request['root_category_id'] = $request->category_id[2];
                foreach ($product->categories()->get() as $cat_pro) {
                    $cat_pro->pivot->delete();
                    if (!isset($request->brand_id)) {
                        $product->brand_id = null;
                        $product->save();
                    }
                }
                $product->categories()->attach($request->category_id);
            }

            if (isset($request->brand_id)) $request['brand_id'] = $request->brand_id;
            else $request['brand_id'] = $product->brand_id;

            $product->update(['name'=>$request->good['name'],'en_name'=>$request->good['en_name'],'brand_id'=>$request['brand_id'],
                'is_sale'=>$request->is_sale, 'isShow'=>$request->isShow, 'internal_use'=>$request->internal_use,
                'length'=>$request->good['length'],'width'=>$request->good['width'],'height'=>$request->good['height'],
                'weight'=>$request->good['weight'],'description'=>$request->good['description'],
                'root_category_id'=>$request['root_category_id']
            ]);

            //tag
            if (isset($request->tag)) {
                $request_tags = $request->tag;
                $list_tags_product = $product->tagNames();

                Product::UpdateTag($list_tags_product, $request_tags, $product);
            }

            if (isset($request->image_path) || isset($request->multiple_image)) {
                Product::storeImagesProduct($request->image_path, $request->multiple_image, $product);
            }

            //reviewStrengthOld
            if (isset($request->reviewStrengthOld)) {
                foreach ($request->reviewStrengthOld as $index => $value) {
                    $review = Review::find($index);
                    $review->update(['text' => $value['text']]);
                }
            }

            //reviewWeaknessOld
            if (isset($request->reviewWeaknessOld)) {
                foreach ($request->reviewWeaknessOld as $index => $value) {
                    $review = Review::find($index);
                    $review->update(['text' => $value['text']]);
                }
            }


            //edit store Rooms
            if(isset($request->store_room_id)){
                $store_rooms = $product->storeRooms()->wherePivot('store_room_id',$request->store_room_id)->first();
                if(isset($request->stock))  $stock = $request->stock ;  else $stock = null;

                if (isset($store_rooms) && $store_rooms != '') {

                    $store_room_discount = $request->discount;
                    if ($store_room_discount > 0) $store_room_discount = str_replace(",", "", $store_room_discount);

                    $store_room_sales_price = $request->sales_price;
                    if ($store_room_sales_price > 0) $store_room_sales_price = str_replace(",", "", $store_room_sales_price);

                    $store_room_purchase_price = $request->purchase_price;
                    if ($store_room_purchase_price > 0) $store_room_purchase_price = str_replace(",", "", $store_room_purchase_price);

                    $expire_date = Helper::convert($request->expire_date);
                    $expire_date = Verta::parse($expire_date);
                    $expire_date = $expire_date->formatGregorian('Y-m-d H:i:s');
                    $store_rooms->pivot->update(
                        [
                            'category_store_room_id' => $request->category_store_room_id,
                            'storage_store_room_id' => $request->storage_store_room_id,
                            'row_num' => $request->row_num,
                            'col_num' => $request->col_num,
                            'discount' => $store_room_discount,
                            'order_point' => $request->order_point,
                            'order_day' => $request->order_day,
                            'expire_alert' => $request->expire_alert,
                            'expire_date' => $expire_date,
                            'measurement_unit_id' => $request->measurement_unit_id,
                            'purchase_type' => $request->purchase_type,
                            'pay_type' => $request->pay_type,
                            'stock' => $stock,
                            'sales_price' => $store_room_sales_price,
                            'purchase_price' => $store_room_purchase_price,
                            'barcode' => $request->barcode,
                            'main_unit_id' => $request->main_unit_id,
                            'sub_unit_id' => $request->sub_unit_id,
                            'main_unit_value' => $request->main_unit_value,
                            'sub_unit_value' => $request->sub_unit_value,
                            'storage_num' => $request->storage_num
                        ]);
                }
            }

            return redirect("/dashboard/storeRoomGood/$request->store_room_id")->with(['update' => 'success']);

        }


        return redirect()->back()->with(['update' => 'warning']);
    }

    //show store Room Information in modal and edit it in seller modal

    /**
     * @param $product_id
     * @param $id
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application|\Illuminate\Http\RedirectResponse
     */
    public function productStoreRoomByProductId($product_id, $id)
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

    //show store Room Information in modal and update it in seller modal

    /**
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\RedirectResponse|void
     */
    public function updatePivotProductStoreRoom(Request $request, $id)
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
                            'expire_date' => Helper::vertaToMiladi($request->expire_date),
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
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroyProductStoreRoom(Request $request, $id)
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

}
