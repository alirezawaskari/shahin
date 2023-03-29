<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Attribute;
use App\Models\AttributeGroup;
use App\Models\AttributeItem;
use App\Models\Brand;
use App\Models\CategoryNews;
use App\Models\CategoryStoreRoom;
use App\Models\Center;
use App\Models\ChangeStoreRoom;
use App\Models\CountingUnit;
use App\Models\CouponItem;
use App\Models\ProductValidation;
use App\Models\Review;
use App\Models\Gallery;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\StorageStoreRoom;
use App\Models\StoreRoom;
use App\Models\Tag;
use App\Models\User;
use App\Models\Validation;
use Carbon\Carbon;
use Hekmatinasser\Verta\Verta;
use Illuminate\Http\Request;
use App\Models\Category;
use File;
use Cviebrock\EloquentSluggable\Services\SlugService;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    protected $products;

    public function __construct(Product $products)
    {
        $this->product = $products;
    }


    public function find()
    {
        return view('Admin.product.findProduct');
    }

    /**
     * Display a listing of the Materials products.
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $products = Product::where('type', 0)->orderBy('id', 'DESC');
        $categories = Category::where('depth', 2)->get();
        $create_link = route('createMaterials');
        $queries = [];
        $columns = ['id', 'name','brand_id'];

        foreach ($columns as $column) {
            if ($request->has($column)) {
                if ($request->$column != null) {
                    if ($column == 'id') {
                        $products = $products->where($column, $request->$column);
                        $queries[$column] = $request->$column;
                    }
                    if ($column == 'name') {
                        $products = $products->where($column, 'like', '%' . $request->$column . '%');
                        $queries[$column] = $request->$column;
                    }
                    if ($column == 'brand_id') {
                        if ($request->$column != 'all') {
                            $products = $products->where($column, $request->$column);
                            $queries[$column] = $request->$column;
                        } else {
                            $queries[$column] = 'all';
                        }
                    }
                }
            }
        }

        $products = $products->paginate(15)->appends($queries);

        foreach ($products as $product) {
            $sellers = [];
            if (($product->type == 0) && $product->storeRooms()->exists()) {
                $field_product_store_room = [];
                $product_store_rooms = $product->storeRooms()->get();
                foreach ($product_store_rooms as $item) {
                    $field_product_store_room['id'] = $item->pivot->id;
                    $field_product_store_room['seller_title'] = StoreRoom::getStoreRoom($item->pivot->store_room_id, 'name');
                    $field_product_store_room['price'] = $item->pivot->sales_price;
                    $field_product_store_room['stock'] = $item->pivot->stock;
                    $product_store_room_id = $item->pivot->id;
                    $field_product_store_room['link'] = route('getProductStoreRoom',['product_id'=>$product->id,'id'=>$product_store_room_id]);
                    $sellers[] = $field_product_store_room;
                }
            }
            $product['sellers'] = $sellers;
        }

        $page_link = "/dashboard/products";

        $brands = [];
        $product_brands = Product::with(['brand'])->where('type', 0)->get();
        foreach ($product_brands as $item) {
            if (isset($item->brand->title)) $brands[$item->brand->id] = $item->brand->title;
        }
        $brands = array_unique($brands);


        if ($request->ajax()) {
            return view('Admin.product.load', ['products' => $products, 'type_id' => 0])->render();
        }
        return view('Admin.product.list', ['products' => $products, 'type_id' => '0', 'brands' => $brands,
            'create_link' => $create_link, 'categories' => $categories, 'page_link' => $page_link,
            'queries' => $queries]);
    }

    /**
     * Show the form for creating a new Materials.
     *
     * @return \Illuminate\Http\Response
     */
    public function createMaterials()
    {
        return $this->handleCreateMaterials(null, null);
    }

    //create Materials By Values
    public function createMaterialsByValues($type, $id)
    {
        return $this->handleCreateMaterials($type, $id);
    }


    public function handleCreateMaterials($type, $id)
    {
        $page_title = 'ایجاد محصول مواد اولیه';
        $store_room_id = null;
        $purchase_invoice_id = null;
        if ($type == 'store_room') {
            $store_room_id = $id;
        }
        $internal_use_material = 0;
        if ($type == 'store_room_internal_use') {
            $store_room_id = $id;
            $internal_use_material = 1;
            $page_title = 'ایجاد محصول مصرف داخلی';
        }
        if ($type == 'factor') {
            $purchase_invoice_id = $id;
        }
        $storage_store_rooms = StorageStoreRoom::all();
        $category_store_rooms = CategoryStoreRoom::all();
        $store_rooms = StoreRoom::all();
        $counting_units = CountingUnit::where('type', 'packing')->get();
        $counting_units_measurement = CountingUnit::where('type', 'measurement')->get();
        $parents = Category::where('parent_id', 0)->get();
        $products = Product::orderBy('id', 'DESC')->get();

        $material_products = Product::orderBy('id', 'DESC')->where('type', 0)->get();

        return view('Admin.product.createMaterials', compact('parents', 'store_rooms',
            'category_store_rooms', 'counting_units', 'products', 'material_products'
            , 'counting_units_measurement', 'storage_store_rooms', 'store_room_id', 'purchase_invoice_id',
            'internal_use_material', 'page_title'));
    }


    /**
     * Store Materials a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function storeMaterials(Request $request)
    {
        $insert_by_store_room = 0;
        if (isset($request->material['product_id']) && $request->material['product_id'] != '') {
            $product = Product::find($request->material['product_id']);

            $request->validate([
                'material.category_store_room_id' => 'required',
                'material.storage_store_room_id' => 'required',
                'material.row_num' => 'required', 'material.col_num' => 'required',
                'material.order_point' => 'required',
                'material.order_day' => 'required',
                'material.expire_alert' => 'required',
                'material.expire_date' => 'required',
                'material.measurement_unit_id' => 'required',
                'material.stock' => 'required',
                'material.main_unit_id' => 'required',
                'material.sub_unit_id' => 'required',
                'material.main_unit_value' => 'required',
                'material.sub_unit_value' => 'required',
                'material.storage_num' => 'required',
            ],
                [
                    'material.category_store_room_id.required' => 'لطفا دسته بندی انبار را انتخاب کنید',
                    'material.storage_store_room_id.required' => 'لطفا محل نگهداری را انتخاب کنید',
                    'material.row_num.required' => 'لطفا شماره سطر را وارد کنید',
                    'material.col_num.required' => 'لطفا شماره ستون را وارد کنید',
                    'material.order_point.required' => 'لطفا نقطه سفارش را وارد کنید',
                    'material.order_day.required' => 'لطفا مدت زمان سفارش را وارد کنید',
                    'material.expire_alert.required' => 'لطفا اعلام انقضا را وارد کنید',
                    'material.expire_date.required' => 'لطفا تاریخ انقضا را وارد کنید',
                    'material.measurement_unit_id.required' => 'لطفا واحد اندازه گیری مصرفی را انتخاب کنید',
                    'material.stock.required' => 'لطفا موجودی را انتخاب کنید',
                    'material.main_unit_id.required' => 'لطفا واحد اصلی را انتخاب کنید',
                    'material.main_unit_value.required' => 'لطفا  تعداد واحد اصلی را وارد کنید',
                    'material.sub_unit_id.required' => 'لطفا واحد فرعی را انتخاب کنید',
                    'material.sub_unit_value.required' => 'لطفا تعداد واحد فرعی را وارد کنید',
                    'material.storage_num.required' => 'لطفا شماره محل نگهداری را وارد کنید',
                ]
            );

        } else {
            $request->validate([
                'material.name' => 'required',
                'material.category_store_room_id' => 'required',
                'material.storage_store_room_id' => 'required',
                'material.row_num' => 'required', 'material.col_num' => 'required',
                'material.order_point' => 'required',
                'material.order_day' => 'required',
                'material.expire_alert' => 'required',
                'material.expire_date' => 'required',
                'material.measurement_unit_id' => 'required',
                'material.stock' => 'required',
                'material.main_unit_id' => 'required',
                'material.sub_unit_id' => 'required',
                'material.main_unit_value' => 'required',
                'material.sub_unit_value' => 'required',
                'material.storage_num' => 'required',
            ],
                [
                    'material.name.required' => 'لطفا عنوان را وارد کنید',
                    'material.category_store_room_id.required' => 'لطفا دسته بندی انبار را انتخاب کنید',
                    'material.storage_store_room_id.required' => 'لطفا محل نگهداری را انتخاب کنید',
                    'material.row_num.required' => 'لطفا شماره سطر را وارد کنید',
                    'material.col_num.required' => 'لطفا شماره ستون را وارد کنید',
                    'material.order_point.required' => 'لطفا نقطه سفارش را وارد کنید',
                    'material.order_day.required' => 'لطفا مدت زمان سفارش را وارد کنید',
                    'material.expire_alert.required' => 'لطفا اعلام انقضا را وارد کنید',
                    'material.expire_date.required' => 'لطفا تاریخ انقضا را وارد کنید',
                    'material.measurement_unit_id.required' => 'لطفا واحد اندازه گیری مصرفی را انتخاب کنید',
                    'material.stock.required' => 'لطفا موجودی را انتخاب کنید',
                    'material.main_unit_id.required' => 'لطفا واحد اصلی را انتخاب کنید',
                    'material.main_unit_value.required' => 'لطفا  تعداد واحد اصلی را وارد کنید',
                    'material.sub_unit_id.required' => 'لطفا واحد فرعی را انتخاب کنید',
                    'material.sub_unit_value.required' => 'لطفا تعداد واحد فرعی را وارد کنید',
                    'material.storage_num.required' => 'لطفا شماره محل نگهداری را وارد کنید',
                ]
            );

            $request['slug'] = SlugService::createSlug(Product::class, 'slug', $request->material['name']);

            $brand_id=null;
            if ($request->material['brand_name_fa'] != null){
                $brand = Brand::create(['title' => $request->material['brand_name_fa'], 'title_en' => $request->material['brand_name_en']]);
                $brand_id=$brand->id;
            }

            $product = Product::create(['name' => $request->material['name'], 'slug' => $request['slug'], 'type' => 0, 'brand_id' => $brand_id]);
        }

        if (\Auth::user()->isAbleTo('product-detail')) {
            // Product with StoreRoom
            if (isset($request->insertByStoreRoom) && $request->insertByStoreRoom == 1) {
                $insert_by_store_room = 1;
            }
            $purchase_invoice_id = null;
            if (isset($request->material['purchase_invoice_id'])) {
                $purchase_invoice_id = $request->material['purchase_invoice_id'];
            }
            $expire_date = Helper::convert($request->material['expire_date']);
            $expire_date = Verta::parse($expire_date);
            $expire_date = $expire_date->formatGregorian( 'Y-m-d H:i:s' );

            $store_room_discount = $request->material['discount'];
            if ($store_room_discount > 0) $store_room_discount = str_replace(",", "", $store_room_discount);

            $store_room_sales_price = $request->material['sales_price'];
            if ($store_room_sales_price > 0) $store_room_sales_price = str_replace(",", "", $store_room_sales_price);

            $store_room_purchase_price = $request->material['purchase_price'];
            if ($store_room_purchase_price > 0) $store_room_purchase_price = str_replace(",", "", $store_room_purchase_price);

            $product->storeRooms()->attach($request->material['store_room_id'],
                [
                    'user_id'=>Auth::user()->id,
                    'category_store_room_id' => $request->material['category_store_room_id'],
                    'storage_store_room_id' => $request->material['storage_store_room_id'],
                    'purchase_invoice_id' => $purchase_invoice_id,
                    'row_num' => $request->material['row_num'],
                    'col_num' => $request->material['col_num'],
                    'order_point' => $request->material['order_point'],
                    'order_day' => $request->material['order_day'],
                    'expire_alert' => $request->material['expire_alert'],
                    'expire_date' => $expire_date,
                    'measurement_unit_id' => $request->material['measurement_unit_id'],
                    'sales_price' => $store_room_sales_price,
                    'purchase_price' => $store_room_purchase_price,
                    'discount' => $store_room_discount,
                    'barcode' => $request->material['barcode'],
                    'main_unit_id' => $request->material['main_unit_id'],
                    'sub_unit_id' => $request->material['sub_unit_id'],
                    'main_unit_value' => $request->material['main_unit_value'],
                    'sub_unit_value' => $request->material['sub_unit_value'],
                    'storage_num' => $request->material['storage_num'],
                    'stock' => $request->material['stock'],
                    'initial_stock' => $request->material['stock']
                ]);

        }

        if (isset($request->internal_use_material)) {
            if ($request->internal_use_material == 1) {
                $product->type = 1;
                $product->internal_use = 1;
                $product->save();
            }
        }

        ChangeStoreRoom::createChangeStoreRoom($product->id, $request->material['store_room_id'],
            StoreRoom::class, $request->material['stock'], 1);

        if ($insert_by_store_room == 1) {

            if($product->internal_use == 1){
                $create_link=route('createMaterialsByValues',['type'=>'store_room_internal_use','id'=>$request->material['store_room_id']]);
            }else{
                $create_link=route('createGoodByValues',['type'=>'store_room','id'=>$request->material['store_room_id']]);
            }

            return redirect($create_link)->with(['create' => 'success']);
        } else {
            return redirect(route('createMaterials'))->with(['create' => 'success']);
        }
    }


    /**
     * Show the form for creating a new Good.
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return $this->handleCreateGood(null, null);
    }

    //create Good By Values
    public function createByValues($type, $id)
    {
        return $this->handleCreateGood($type, $id);
    }

    //handle create Good
    public function handleCreateGood($type, $id)
    {
        $store_room_id = null;
        $center_id = '';
        $purchase_invoice_id = null;
        if ($type == 'store_room') {
            $store_room_id = $id;
        }
        if ($type == 'factor') {
            $purchase_invoice_id = $id;
        }
        if ($type == 'center') {
            $center_id = $id;
        }
        $centers = Center::all();
        $storage_store_rooms = StorageStoreRoom::all();
        $category_store_rooms = CategoryStoreRoom::all();
        $store_rooms = StoreRoom::all();
        $counting_units = CountingUnit::where('type', 'packing')->get();
        $counting_units_measurement = CountingUnit::where('type', 'measurement')->get();
        $parents = Category::where('parent_id', 1)->get();

        $tags = Tag::all();
        $category_center_id = 0;

        if (User::checkManager() != null) {
            $center_id = User::checkManager();
            $center = Center::find($center_id);
            if (isset($center) && $center->centerable_type == Category::class) $category_center_id = $center->centerable_id;
        }

        return view('Admin.product.createGood', compact('parents', 'centers', 'store_rooms',
            'category_store_rooms', 'counting_units', 'tags', 'category_center_id',
            'storage_store_rooms', 'counting_units_measurement', 'store_room_id', 'purchase_invoice_id', 'center_id'));
    }

    //store good
    public function store(Request $request)
    {
        $insertByStoreRoom = 0;
        if (isset($request->image_name_good) && $request->image_name_good != null) {
            //center_id store_manager or admin
            if (isset($request->good['center_id']) || isset($request->good['store_room_id'])) {

                $productattributeable_id = 0;
                $productattributeable_type = '';

                //store_room
                if (isset($request->type_good_selected) && $request->type_good_selected == 0) {
                    Product::validationStoreRoomGood($request);
                }

                //center_id maybe choose by admin or store manager
                if (isset($request->good['center_id'])) {
                    Product::validationCenterGoodAdmin($request);
                    $productattributeable_id = $request->good['center_id'];
                    $productattributeable_type = Center::class;
                }

                $request['is_sale'] = isset($request->good['is_sale']) ? 1 : 0;
                $request['internal_use'] = isset($request->good['internal_use']) ? 1 : 0;
                $request['slug'] = SlugService::createSlug(Product::class, 'slug', $request->good['name']);

                $all_father = Category::findAllFather($request->choose_category_id);
                array_push($all_father, $request->choose_category_id);
                $request->category_id = $all_father;
                if (isset($request->category_id)) $request['root_category_id'] = $request->category_id[2];

                $product = Product::create(['name' => $request->good['name'], 'en_name' => $request->good['en_name']
                    , 'slug' => $request['slug'], 'brand_id' => $request->good['brand_id']
                    , 'is_sale' => $request['is_sale'], 'status' => 1,
                    'internal_use' => $request['internal_use'],
                    'root_category_id' => $request['root_category_id']
                    , 'type' => 1, 'length' => $request->good['length'],
                    'width' => $request->good['width'], 'height' => $request->good['height'],
                    'weight' => $request->good['weight'], 'description' => $request->good['description']
                ]);

                if (isset($request->category_id)) $product->categories()->attach($request->category_id);

                //store_room
                if ($request->type_good_selected == 0) {

                    $expire_date = Helper::convert($request->good['expire_date']);
                    $expire_date = Verta::parse($expire_date);
                    $expire_date = $expire_date->formatGregorian('Y-m-d H:i:s');

                    $purchase_invoice_id = null;
                    if (isset($request->good['purchase_invoice_id'])) {
                        $purchase_invoice_id = $request->good['purchase_invoice_id'];
                    }
                    if (isset($request->insertByStoreRoom) && $request->insertByStoreRoom == 1) {
                        $insertByStoreRoom = 1;
                    }
                    $store_room_discount = $request->good['discount'];
                    if ($store_room_discount > 0) $store_room_discount = str_replace(",", "", $store_room_discount);

                    $store_room_sales_price = $request->good['sales_price'];
                    if ($store_room_sales_price > 0) $store_room_sales_price = str_replace(",", "", $store_room_sales_price);

                    $store_room_purchase_price = $request->good['purchase_price'];
                    if ($store_room_purchase_price > 0) $store_room_purchase_price = str_replace(",", "", $store_room_purchase_price);


                    // Product with StoreRoom
                    $product->storeRooms()->attach($request->good['store_room_id'],
                        ['user_id'=>Auth::user()->id,
                        'category_store_room_id' => $request->good['category_store_room_id'],
                        'storage_store_room_id' => $request->good['storage_store_room_id'],
                        'purchase_invoice_id' => $purchase_invoice_id,
                        'row_num' => $request->good['row_num'],
                        'col_num' => $request->good['col_num'],
                        'discount' => $store_room_discount,
                        'order_point' => $request->good['order_point'],
                        'order_day' => $request->good['order_day'],
                        'expire_alert' => $request->good['expire_alert'],
                        'expire_date' => $expire_date,
                        'measurement_unit_id' => $request->good['measurement_unit_id'],
                        'purchase_type' => $request->good['purchase_type'],
                        'pay_type' => $request->good['pay_type'],
                        'sales_price' => $store_room_sales_price,
                        'purchase_price' => $store_room_purchase_price,
                        'barcode' => $request->good['barcode'],
                        'main_unit_id' => $request->good['main_unit_id'],
                        'sub_unit_id' => $request->good['sub_unit_id'],
                        'main_unit_value' => $request->good['main_unit_value'],
                        'sub_unit_value' => $request->good['sub_unit_value'],
                        'storage_num' => $request->good['storage_num']
                    ]);

                    foreach ($product->storeRooms()->latest()->get() as $product_store_room){
                        $product_store_room_id = $product_store_room->pivot->id;
                    }

                    if (isset($product_store_room_id)) {
                        $productattributeable_id = $product_store_room_id;
                        $productattributeable_type = ProductStoreRoom::class;
                    }
                }

                //center
                if (isset($request->good['center_id'])) {
                    foreach ($request->good['center_id'] as $key => $value) {
                        $center = Center::find($value);
                        $product->centers()->attach($value, ['purchase_type' => $center->purchase_type,
                            'pay_type' => $center->pay_type,'user_id'=>Auth::user()->id
                        ]);
                    }
                }

                Product::saveImageGood($request, $product);
                Product::saveAttributeGood($request, $product, $productattributeable_id, $productattributeable_type);

                //tag
//                if (isset($request->tag) && $request->tag) $product->tag(['tag_name' => $request->tag]);

                if(isset($request->createByAdmin)){ $product->status=3 ;$product->save();}

                Product::saveReviewsGood($request, $product, $productattributeable_id, $productattributeable_type);


                if ($insertByStoreRoom == 1) {
                    return redirect(route('createGoodByValues',['type'=>'store_room','id'=>$request->good['store_room_id']]))->with(['create' => 'success']);
                }
                if(isset($request->createByCenter) && $request->createByCenter != null){
                    return redirect(route('createGoodByValues',['type'=>'center','id'=>$request->createByCenter ]))->with(['create' => 'success']);
                }
                else {
                    return redirect(route('createGood'))->with(['create' => 'success']);
                }

            } else {
                return redirect(route('createGood'))->with(['failByMessage' => 'بایستی مرکز و یا انبار برای کالا مشخص شود']);
            }
        } else {
            return redirect(route('createGood'))
                ->with(['failByMessage' => 'ثبت عکس  محصول ضروری می باشد.']);
        }
    }


    /**
     * Display the list of goods.
     *
     * @param  int $type_id
     * @return \Illuminate\Http\Response
     */
    public function show($id, Request $request)
    {
        $center_id = null;
        if (User::checkManager() != null) {
            $center_id = User::checkManager();
            $products = Product::with('centers')->whereHas('centers', function ($query) use ($center_id) {
                $query->where('center_id', $center_id);
            })->select('id', 'name', 'image', 'type', 'isShow', 'root_category_id', 'internal_use', 'brand_id', 'status')
                ->orderBy('id', 'DESC')->where('type', $id);
            $categories = Category::categoryDepth($center_id);
        } else {
            $products = Product::select('id', 'name', 'image', 'type', 'isShow', 'root_category_id', 'internal_use', 'brand_id', 'status')
                ->orderBy('id', 'DESC')->where('type', $id);
            $categories = Category::categoryDepth(null);
        }

        $columns = ['id', 'name', 'root_category_id', 'status', 'brand_id'];
        $queries = [];

        foreach ($columns as $column) {
            if ($request->has($column)) {
                if ($request->$column != null) {
                    if ($column == 'id' || $column == 'status') {
                        $products = $products->where($column, $request->$column);
                        $queries[$column] = $request->$column;
                    }
                    if ($column == 'root_category_id' || $column == 'brand_id') {
                        if ($request->$column != 'all') {
                            $products = $products->where($column, $request->$column);
                            $queries[$column] = $request->$column;
                        } else {
                            $queries[$column] = 'all';
                        }
                    }
                    if ($column == 'name' || $column == 'en_name') {
                        $products = $products->where($column, 'like', '%' . $request->$column . '%');
                        $queries[$column] = $request->$column;
                    }
                }

            }
        }
        $brands = [];
        $product_brands = $products->with(['brand'])->get();

        $products = $products->paginate(15)->appends($queries);

        if ($center_id == null) {
            foreach ($products as $product) {
                $sellers = [];
                if (($product->type == 0) && $product->storeRooms()->exists()) {
                    $field_product_store_room = [];
                    $product_store_rooms = $product->storeRooms()->get();
                    foreach ($product_store_rooms as $item) {
                        $field_product_store_room['id'] = $item->pivot->id;
                        $field_product_store_room['seller_title'] = StoreRoom::getStoreRoom($item->pivot->store_room_id, 'name');
                        $field_product_store_room['price'] = $item->pivot->sales_price;
                        $field_product_store_room['stock'] = $item->pivot->stock;
                        $product_store_room_id = $item->pivot->id;
                        $field_product_store_room['link'] = route('getProductStoreRoom',['product_id'=>$product->id,'id'=>$product_store_room_id]);
                        $sellers[] = $field_product_store_room;
                    }
                }

                if (($product->type == 1) && ($product->centers()->exists() || $product->storeRooms()->exists())) {
                    $product_attributes_list = $product->attributes();

                    foreach ($product->storeRooms as $item) {
                        $product_attributes = $product_attributes_list->where('is_price', true)
                            ->wherePivot('productattributeable_type', ProductStoreRoom::class)
                            ->wherePivot('productattributeable_id', $item->pivot->id)->get();
                        $no_price = 0;
                        if ($product_attributes_list->where('is_price', true)->count() == 0) {
                            $product_attributes = ProductAttribute::where('product_id', $product->id)
                                ->where('price', '!=', '')
                                ->where('productattributeable_type', ProductStoreRoom::class)
                                ->where('productattributeable_id', $item->pivot->id)->get();
                            $no_price = 1;
                        }

                        foreach ($product_attributes as $attribute) {
                            if ($no_price == 0) {
                                $attribute = $attribute->pivot;
                                if ($attribute->attribute_id != null) $field_product_attribute['attribute_id'] = Attribute::find($attribute->attribute_id)->title;
                                if ($attribute->attribute_item_id != null) $field_product_attribute['attribute_item_id'] = AttributeItem::find($attribute->attribute_item_id)->title;
                            }
                            $field_product_attribute['price'] = $attribute->price;
                            $field_product_attribute['stock'] = $attribute->stock;
                            $field_product_attribute['id'] = $item->pivot->id;
                            $field_product_attribute['seller_title'] = StoreRoom::find($item->pivot->store_room_id)->name;
                            $attribute_product_id = $attribute->id;
                            $field_product_attribute['link'] = "/dashboard/product/price/$attribute_product_id/edit";
                            $sellers[] = $field_product_attribute;
                        }
                    }


                    foreach ($product->centers as $item) {

                        $product_attributes = $product_attributes_list->where('is_price', true)
                            ->wherePivot('productattributeable_type', Center::class)
                            ->wherePivot('productattributeable_id', $item->pivot->center_id)->get();
                        $no_price = 0;
                        if ($product_attributes_list->where('is_price', true)->count() == 0) {
                            $product_attributes = ProductAttribute::where('product_id', $product->id)
                                ->where('price', '!=', '')
                                ->where('productattributeable_type', Center::class)
                                ->where('productattributeable_id', $item->pivot->center_id)->get();
                            $no_price = 1;
                        }

                        foreach ($product_attributes as $attribute) {
                            if ($no_price == 0) {
                                $attribute = $attribute->pivot;
                                if ($attribute->attribute_id != null) $field_product_attribute['attribute_id'] = Attribute::find($attribute->attribute_id)->title;
                                if ($attribute->attribute_item_id != null) $field_product_attribute['attribute_item_id'] = AttributeItem::find($attribute->attribute_item_id)->title;
                            }
                            $field_product_attribute['price'] = $attribute->price;
                            $field_product_attribute['stock'] = $attribute->stock;
                            $field_product_attribute['status'] = $item->pivot->status;
                            $field_product_attribute['id'] = $item->pivot->id;
                            $field_product_attribute['seller_title'] = Center::find($item->pivot->center_id)->title;
                            $attribute_product_id = $attribute->id;
                            $field_product_attribute['link'] = "/dashboard/product/price/$attribute_product_id/edit";
                            $sellers[] = $field_product_attribute;
                        }
                    }
                }

                $product['sellers'] = $sellers;

            }
        }

        foreach ($product_brands as $item) {
            if (isset($item->brand->title)) $brands[$item->brand->id] = $item->brand->title;
        }

        $brands = array_unique($brands);

        if ($request->ajax()) {
            return view('Admin.product.load', ['products' => $products, 'type_id' => $id])->render();
        }
        $page_link = route('product.index');
        $create_link = route('createMaterials');
        if($id == 1){
            $page_link = route('product.show',1);
            $create_link = route('createGood');
        }

        return view('Admin.product.list', ['products' => $products, 'type_id' => $id
            , 'categories' => $categories, 'queries' => $queries, 'center_id' => $center_id, 'brands' => $brands,
            'create_link'=>$create_link,'page_link'=>$page_link]);
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $product = Product::find($id);
        $center_product = [];

        if ($product->type == 0) {
            $brand = Brand::where('id', $product->brand_id)->first();
            return view('Admin.product.edit', compact('product', 'brand'));
        }

        if ($product->type == 1) {
            $product_by_center = 'false';
            $center_id = null;
            $reviews_strength = $product->reviews()->where('type', 0)->get();
            $reviews_weakness = $product->reviews()->where('type', 1)->get();
            $tags = Tag::pluck('name');

            $parents = Category::where('parent_id', 1)->get();
            $categories_product = $product->categories()->orderBy('category_product.id', 'ASC')->get();

            $parent_category_id = '';
            $category_center = [];
            $category_center_id = '';
            if (User::checkManager() != null) {
                $center_id = User::checkManager();
                $product_by_center = 'true';
                $center = Center::find($center_id);
                $category_center_id = $center->centerable_id;
                $category_center = Category::where('parent_id', $category_center_id)->get();
                $category = Category::find($category_center_id);
                if ($category) {
                    $parent_category = Category::where('id', $category->parent_id)->select('id')->first();
                    $parent_category_id = $parent_category->id;
                }
            }

            $centers = Center::select('id', 'title')
                ->where('centerable_type', Category::class)->get();
            $product_centers_ids = $product->centers()->pluck('centers.id')->toArray();

            $last_category_id = Category::latestCategory($product);

            //ویژگی مجاز برای تنوع
            $allowed_attribute = Attribute::allowedAttributeInCategory($last_category_id);

            $attributeGroups = AttributeGroup::where('category_id', $last_category_id)->get();
            $attributes = [];
            if (isset($attributeGroups) && count($attributeGroups) > 0) {
                foreach ($attributeGroups as $item) {
                    $attributes[$item->id] = $item->attributes()->where('is_price', 0)->get();
                }
            }
            $product_attributes = $product->attributes()->where('is_price', 0)->get();

            $brands = Category::find($last_category_id);
            if (!is_null($brands)){
                $brands=$brands->brands;
            }else{
                $brands=array();
            }
            $product_validation = $product->productValidations;
            $validations = Validation::all();

            $store_rooms = StoreRoom::all();
            $counting_units = CountingUnit::where('type', 'packing')->get();
            $counting_units_measurement = CountingUnit::where('type', 'measurement')->get();
            $category_store_rooms = CategoryStoreRoom::all();
            $storage_store_rooms = StorageStoreRoom::all();


            return view('Admin.product.edit', compact('product', 'parents', 'tags', 'reviews_weakness'
                , 'reviews_strength', 'categories_product', 'center_product', 'product_by_center', 'parent_category_id',
                'category_center', 'category_center_id', 'centers', 'product_centers_ids', 'center_id', 'brands',
                'product_validation', 'store_rooms', 'counting_units', 'counting_units_measurement',
                'category_store_rooms', 'storage_store_rooms', 'attributes', 'product_attributes', 'validations',
                'allowed_attribute'));
        }

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
        $product = Product::find($id);

        if ($product->type == 0) {
            $request['internal_use'] = isset($request['internal_use']) ? $request['internal_use'] : 0;
            $request['slug'] = SlugService::createSlug(Product::class, 'slug', $request->name);

            $brand_id = null;

            $brand = Brand::find($product->brand_id);
            if (isset($brand)) {
                $brand->update(['title' => $request->brand_name_fa, 'title_en' => $request->brand_name_en]);
                $brand_id = $brand->id;
            } else if ($request->brand_name_fa != null) {
                $data_brand = Brand::create(['title' => $request->brand_name_fa, 'title_en' => $request->brand_name_en]);
                $brand_id = $data_brand->id;
            }

            $product->update(['name' => $request->name, 'slug' => $request['slug'], 'type' => 0,
                'internal_use' => $request['internal_use'], 'brand_id' => $brand_id]);


            return redirect('/dashboard/products')->with(['update' => 'success']);
        }

        if ($product->type == 1) {
            $request->validate(['good.name' => 'required'], ['good.name.required' => 'لطفا عنوان را وارد کنید']);

            //user is not store manager
            if (User::checkManager() == null) {
                $request['reference_price'] = str_replace(',', '', $request->reference_price);
                $request['isShow'] = isset($request['isShow']) ? $request['isShow'] : 0;
                $request['is_sale'] = isset($request->good['is_sale']) ? $request->good['is_sale'] : 0;
                $request['internal_use'] = isset($request['internal_use']) ? $request['internal_use'] : 0;

                //admin:The edited product goes into product-edit review mode
                if ($product->status == 4) $request['status'] = 6;
            }
            if (User::checkManager() != null) {
                //manager : The product is confirmed and edited
                if ($product->status == 5) $request['status'] = 4;
            }

            $productattributeable_id = 0;
            $productattributeable_type = '';

            //center_id maybe choose by admin or store manager
            if (isset($request->centers_id)) {
                $productattributeable_id = $request->centers_id;
                $productattributeable_type = Center::class;
            }
            //store_room
            if (isset($request->product_store_room_id)) {
                $productattributeable_id = $request->product_store_room_id;
                $productattributeable_type = ProductStoreRoom::class;
            }

            Product::saveImageGood($request, $product);
            Product::updateAttributeGood($request, $product, $productattributeable_id, $productattributeable_type);

            if ($request->good['name'] != null)
                $request['slug'] = SlugService::createSlug(Product::class, 'slug', $request->good['name']);

            $product = Product::find($id);

            //category
            if (isset($request->choose_category_id)) {
                $all_father = Category::findAllFather($request->choose_category_id);
                array_push($all_father, $request->choose_category_id);
                $request->category_id = $all_father;
                $request['root_category_id'] = $request->category_id[2];
                foreach ($product->categories()->get() as $cat_pro) {
                    $cat_pro->pivot->delete();
                    if (!isset($request->good['brand_id'])) {
                        $product->brand_id = null;
                        $product->save();
                    }
                }
                $product->categories()->attach($request->category_id);
            }

            if (isset($request->good['brand_id'])) $request['brand_id'] = $request->good['brand_id'];

            $product->update(['name' => $request->good['name'], 'en_name' => $request->good['en_name'],
                'is_sale' => $request->is_sale, 'isShow' => $request->isShow, 'internal_use' => $request->internal_use,
                'length' => $request->good['length'], 'width' => $request->good['width'], 'height' => $request->good['height'],
                'weight' => $request->good['weight'], 'description' => $request->good['description'], 'root_category_id' => $request['root_category_id']
            ]);

            if (isset($request->centers_id)) {
                foreach ($request->centers_id as $center_id) {
                    $center = Center::find($center_id);
                    //collect all inserted record IDs
                    $array[$center_id] = ['purchase_type' => $center->purchase_type, 'pay_type' => $center->pay_type];
                }
                $product->centers()->sync($array, true);//delete old entries = true
            } else {
                $product->centers()->sync([], true);//delete old entries = true
            }

            //tag
//            if (isset($request->tag)) {
//                $request_tags = $request->tag;
//                $list_tags_product = $product->tagNames();
//
//                Product::UpdateTag($list_tags_product, $request_tags, $product);
//            }

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
            if (isset($request->store_room_id)) {
                $store_rooms = $product->storeRooms()->wherePivot('store_room_id', $request->store_room_id)->first();
                if (isset($request->stock)) $stock = $request->stock; else $stock = null;

                if (isset($store_rooms) && $store_rooms != '') {

                    $expire_date = Helper::convert($request->expire_date);
                    $expire_date = Verta::parse($expire_date);
                    $expire_date = $expire_date->formatGregorian('Y-m-d H:i:s');

                    $store_room_discount = $request->discount;
                    if ($store_room_discount > 0) $store_room_discount = str_replace(",", "", $store_room_discount);

                    $store_room_sales_price = $request->sales_price;
                    if ($store_room_sales_price > 0) $store_room_sales_price = str_replace(",", "", $store_room_sales_price);

                    $store_room_purchase_price = $request->purchase_price;
                    if ($store_room_purchase_price > 0) $store_room_purchase_price = str_replace(",", "", $store_room_purchase_price);

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

            return redirect("/dashboard/products/$product->type")->with(['update' => 'success']);

        }

    }



    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id, Request $request)
    {
        if ($id) {
            $product = Product::find($id);
            //all records in tables belongs to product be delete
            //table : center_product
            //table : products_attribute
            //table : product_storeRooms
            //table : galleries
            //table : changeStoreRoom
            //image product
            //table : product

            if (isset($product)) {
                if (isset($request->center_id)) {
                    //حذف از طریق لیست محصولات هر مرکز
                    if (isset($product->centers)) {
                        $center_products = $product->centers()->where('center_id', $request->center_id)->get();
                        foreach ($center_products as $center_product) {
                            if (isset($center_product->pivot)) $center_product->pivot->delete();
                        }
                        $products_attributes = $product->attributes()->where('productattributeable_id', $request->center_id)
                            ->where('productattributeable_type', Center::class)->get();
                        foreach ($products_attributes as $products_attribute) {
                            if (isset($products_attribute->pivot)) $products_attribute->pivot->delete();
                        }
                    }

                    $product_storeRooms = $product->storeRooms->count();
                    $product_centers = $product->centers->count();

                    if ($product_storeRooms == 0 && $product_centers == 0) {
                        $product->isShow = 0;
                        $product->save();
                        return redirect()->back()->with(['delete' => 'success']);
                    }
                }
                else {
                    $remove_store_room = false;
                    $remove_center = false;

                    if ($product->type == 0) { $remove_store_room = true; }
                    if ($product->type == 1) { $remove_store_room = true; $remove_center = true; }

                    if ($remove_center == true) {
                        if (isset($product->centers)) {
                            foreach ($product->centers()->get() as $center) {
                                $products_attributes = $product->attributes()->where('productattributeable_id', $center->id)
                                    ->where('productattributeable_type', Center::class)->get();
                                foreach ($products_attributes as $products_attribute) {
                                    if (isset($products_attribute->pivot)) $products_attribute->pivot->delete();
                                }
                                $center->pivot->delete();
                            }
                        }
                    }
                    if ($remove_store_room == true) {
                        if (isset($product->storeRooms)) {

                            foreach ($product->storeRooms()->get() as $store_room) {
                                $products_attributes = $product->attributes()->where('productattributeable_id', $store_room->id)
                                    ->where('productattributeable_type', StoreRoom::class)->get();
                                foreach ($products_attributes as $products_attribute) {
                                    if (isset($products_attribute->pivot)) $products_attribute->pivot->delete();
                                }
                                $store_room->pivot->delete();
                            }

                        }
                    }
                    if ($product->galleries()) {
                        foreach ($product->galleries()->get() as $item) {
                            if (\File::exists(public_path($item->image))) {
                                \File::delete(public_path($item->image));
                            }
                            $item->delete();
                        }
                    }

                    if (\File::exists(public_path($product->image))) {
                        \File::delete(public_path($product->image));
                    }

                    if($product->changeStoreRooms()){
                        $product->changeStoreRooms()->delete();
                    }

                    $type = $product->type;
                    $product->delete();
                    if ($type == 0) {
                        return redirect('/dashboard/products')->with(['update' => 'success']);
                    }
                    return redirect("/dashboard/products/$type")->with(['update' => 'success']);
                }
            }
        }
        return redirect()->back();

    }

    //remove Image product of database
    public function removeImgGallery(Request $request)
    {
        if (isset($request->gallery_id)) {
            $gallery = Gallery::find($request->gallery_id);
            if ($gallery) {
                if (\File::exists(public_path($gallery->image))) {
                    \File::delete(public_path($gallery->image));
                }
                $gallery->delete();
            }
            return ['status' => 'true'];
        }
        return ['status' => 'false'];
    }

    //remove Review product of database
    public function removeReview(Request $request)
    {
        if (isset($request->id)) {
            $review = Review::find($request->id);
            if ($review) $review->delete();
            return ['status' => true];
        }
        return ['status' => false];
    }


    //start transfer product

    //search transfer product - return category of center
    public function searchTransferProduct(Request $request)
    {
        $child_categories = [];
        if (User::checkManager() != null) {
            $center_id = User::checkManager();
            $center = Center::find($center_id);
            $child_categories = Category::select('id', 'title')->where('parent_id', $center->centerable_id)->get();
        }
        return ['child' => $child_categories];
    }

    public function selectTransferProduct(Request $request)
    {
        if (User::checkManager() != null) {
            $center_id = User::checkManager();
            $center = Center::find($center_id);
            if (isset($center)) {
                $category_id = $request->id;

                $pro_centers_ids = Product::with('centers')->whereHas('centers', function ($query) use ($center_id) {
                    $query->where('centers.id', $center_id);
                })->pluck('id');

                $products = Product::where('name', 'like', '%' . $request->text . '%')->
                select('id', 'name', 'image', 'root_category_id', 'isShow', 'status')->where('type', 1)
                    ->where('status', 5)->where('isShow', 1);

                $products = $products->where('root_category_id', $category_id)->whereNotIn('id', $pro_centers_ids);
                $products = $products->orderBy('id', 'DESC');
                $view = view('Admin.partials.transferProductList', ['products' => $products->get()]);
                $view_result = $view->render();
                return ['view_result' => $view_result];
            }
        }

        if (\Auth::user()->isAbleTo('product-detail')) {
            $products = Product::select('id', 'name', 'image', 'root_category_id')->where('isShow', 1)->where('status', 5);
            $products = $products->where('name', 'like', '%' . $request->text . '%')
                ->orderBy('id', 'DESC');
            $view = view('Admin.partials.transferProductList', ['products' => $products->get()]);
            $view_result = $view->render();
            return ['view_result' => $view_result];
        }
    }

    //New seller for the goods in the form of transfer
    public function centerSellToo(Request $request)
    {
        //center by manager center
        if (User::checkManager() != null) {
            $center_id = User::checkManager();
            $center = Center::find($center_id);
            if (isset($center)) {
                $product = Product::find($request->product_id);
                $product->centers()->attach($center_id, ['purchase_type' => $center->purchase_type,
                    'pay_type' => $center->pay_type,'user_id'=>Auth::user()->id
                ]);
                return ['status' => true];
            }
        }

        return ['status' => false];

    }

    //end transfer product

    public function status($id, $status_id)
    {
        $product = Product::find($id);
        if ($product) {
            $product->status = $status_id;
            if ($product->status == 5) {
                $product->message = null;
            }
            if ($product->status == 8) {
                $product->message = 'بنا بر صلاح دید تیم بررسی ، محصول حذف شده است';
            }
            $product->save();
        }
        return redirect()->back();
    }

    public function visit($product_id)
    {
        return $this->handleVisitGood(null, null, $product_id);
    }

    //create Good By Values
    public function visitByValues($type, $id, $product_id)
    {
        return $this->handleVisitGood($type, $id, $product_id);
    }

    public function handleVisitGood($type, $id, $product_id)
    {
        $store_room_id = null;
        $center_id = '';
        $purchase_invoice_id = null;
        if ($type == 'store_room') {
            $store_room_id = $id;
        }
        if ($type == 'factor') {
            $purchase_invoice_id = $id;
        }
        if ($type == 'center') {
            $center_id = $id;
        }
        $product = Product::find($product_id);
        if($product->status == 7){
            $product->msg = Product::messageStatus($product->id, 7);
        }

        $storage_store_rooms = StorageStoreRoom::all();
        $category_store_rooms = CategoryStoreRoom::all();
        $store_rooms = StoreRoom::all();
        $counting_units = CountingUnit::where('type', 'packing')->get();
        $counting_units_measurement = CountingUnit::where('type', 'measurement')->get();
        $parents = Category::where('parent_id', 1)->get();

        $tags = Tag::all();
        $category_center_id = 0;

        $categories_product = $product->categories()->orderBy('category_product.id', 'ASC')->get();

        $last_category_id = Category::latestCategory($product);
        $brands = Category::find($last_category_id)->brands;
        $attributeGroups = AttributeGroup::where('category_id', $last_category_id)->get();
        $attributes = [];
        if (isset($attributeGroups) && count($attributeGroups) > 0) {
            foreach ($attributeGroups as $item) {
                $attributes[$item->id] = $item->attributes()->where('is_price', 0)->get();
            }
        }

        $reviews_strength = $product->reviews()->where('type', 0)->get();
        $reviews_weakness = $product->reviews()->where('type', 1)->get();

        $centers = Center::select('id', 'title')
            ->where('centerable_type', Category::class)->get();
        $product_centers_ids = $product->centers()->pluck('centers.id')->toArray();

        $product_attributes = $product->attributes()->where('is_price', 0)->get();

        $validations = Validation::all();

        $product_validations_count = $product->productValidations()->count();

        $similar_products_available = Product::where('name', $product->name)
            ->where('en_name', $product->en_name)
            ->where('brand_id', $product->brand_id)
            ->where('is_sale', $product->is_sale)
            ->where('root_category_id', $product->root_category_id)
            ->where('type', $product->type)
            ->where('description', $product->description)
            ->where('length', $product->length)
            ->where('width', $product->width)
            ->where('height', $product->height)
            ->where('weight', $product->weight)
            ->where('id', '!=', $product->id)
            ->where('status', 5)->get();

        $product_by_center = 'false';
        if (User::checkManager() != null) {
            $product_by_center = 'true';
        }

        return view('Admin.product.visit', compact('parents', 'centers', 'store_rooms',
            'category_store_rooms', 'counting_units', 'tags', 'category_center_id',
            'storage_store_rooms', 'counting_units_measurement', 'store_room_id', 'purchase_invoice_id', 'center_id'
            , 'product', 'categories_product', 'brands', 'reviews_strength', 'reviews_weakness', 'centers',
            'product_centers_ids', 'product_attributes', 'validations', 'attributes',
            'similar_products_available', 'product_by_center','product_validations_count'));
    }

    public function assignSimilarProduct(Request $request)
    {
        $product = Product::find($request->id);
        if ($product) {
            $product->message = $request->similar_id;
            $product->status = 7;
            $product->save();

            $msg = Product::messageStatus($request->id, 7);

            return response(['status' => '200', 'msg' => $msg]);
        }
        return response(['status' => 400]);
    }

    public function getProduct(Request $request)
    {
        $product = Product::select('id', 'message', 'status')->where('id', $request->id)->first();
        if ($product->status == 7) {
            $product['message'] = Product::messageStatus($product->message, 7);
        }
        return $product->message;
    }

    public function removeImageProduct(Request $request)
    {
        if (isset($request->id)) {
            $product = Product::find($request->id);
            if ($product) {
                if (\File::exists(public_path($product->image))) {
                    \File::delete(public_path($product->image));
                }
                $product->image = null;
                $product->save();
            }
            return ['status' => 'true'];
        }
        return ['status' => 'false'];
    }

    public function saveChangeVisitProduct(Request $request)
    {
        if (isset($request->id)) {
            if (isset($request->reference_price))
                $request['reference_price'] = str_replace(',', '', $request->reference_price);
            $product = Product::find($request->id);
            if ($product) {
                $product->isShow = $request->isShow;
                $product->is_sale = $request->is_sale;
                $product->internal_use = $request->internal_use;
                $product->reference_price = $request->reference_price;
                $product->save();
                return ['status' => 'true'];
            }
            return ['status' => 'false'];
        }
        return null;
    }

    public function editInformation($id, Request $request)
    {
        $request['no_stock'] = isset($request['no_stock']) ? $request['no_stock'] : 0;
        $center_id = null;
        if (User::checkManager() != null) {
            $center_id = User::checkManager();
            $product_ids = Product::with('centers')->whereHas('centers', function ($query) use ($center_id) {
                $query->where('center_id', $center_id);
            })->select('id', 'name', 'image', 'type', 'isShow', 'root_category_id', 'internal_use', 'brand_id', 'status')
                ->orderBy('id', 'DESC')->where('type', $id)->pluck('id');

            $product_attributes = ProductAttribute::whereIn('product_id', $product_ids)
                ->where('productattributeable_id', $center_id)
                ->where('productattributeable_type', Center::class)
                ->where('price', '!=', null)->orderBy('id', 'DESC');


            $columns = ['is_show', 'root_category_id', 'product_name', 'seller_code', 'id', 'product_id', 'no_stock'];
            $queries = [];

            foreach ($columns as $column) {
                if ($request->has($column)) {

                    if ($request->$column != null && ($column == 'no_stock' && $request['no_stock'] != 0)) {
                        $product_attributes = $product_attributes->where('stock', '=', 0);
                        $queries['no_stock'] = $request->$column;
                    }
                    if ($request->$column != null && ($column == 'product_id' || $column == 'id' || $column == 'seller_code')) {
                        $product_attributes = $product_attributes->where($column, $request->$column);
                        $queries[$column] = $request->$column;
                    }
                    if ($request->$column != null && $column == 'is_show') {
                        if ($request->$column != 'all') {
                            $product_attributes = $product_attributes->where($column, $request->$column);
                            $queries[$column] = $request->$column;
                        } else {
                            $queries[$column] = 'all';
                        }
                    }
                    if ($request->$column != null && $column == 'product_name') {
                        $product_attributes->with('product')->whereHas('product', function ($query) use ($column, $request) {
                            $query->where('name', 'like', '%' . $request->$column . '%');
                        });
                        $queries[$column] = $request->$column;
                    }
                    if ($request->$column != null && $column == 'root_category_id') {
                        if ($request->$column != 'all') {
                            $product_attributes->with('product')->whereHas('product', function ($query) use ($column, $request) {
                                $query->where('root_category_id', $request->$column);
                            });
                            $queries[$column] = $request->$column;
                        } else {
                            $queries[$column] = 'all';
                        }
                    }
                }
            }

            $product_attributes = $product_attributes->paginate(15)->appends($queries);

            foreach ($product_attributes as $product_attribute) {
                if ($product_attribute->product->brand_id) {
                    $brand = Brand::find($product_attribute->product->brand_id);
                    if (isset($brand)) {
                        $product_attribute['brand_title'] = $brand->title;
                        $product_attribute['brand_title_en'] = Brand::find($product_attribute->product->brand_id)->title_en;
                    }
                }
                $product_attribute['able_to_edit'] = CouponItem::hasCouponByProductAttributeId($product_attribute->id);

            }

            $categories = Category::categoryDepth($center_id);

            return view('Admin.product.editInformation', ['categories' => $categories, 'queries' => $queries,
                'center_id' => $center_id, 'product_attributes' => $product_attributes, 'type_id' => 1]);
        }
    }

    public function removeImageCreated(Request $request)
    {
        if (isset($request->image_name_good)) {
            if (\File::exists(public_path($request->image_name_good))) {
                \File::delete(public_path($request->image_name_good));
            }
            return ['status' => true];
        }
        return ['status' => false];
    }

}
