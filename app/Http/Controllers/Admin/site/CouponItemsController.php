<?php

namespace App\Http\Controllers\Admin\site;

use App\Http\Controllers\Controller;
use App\Models\Attribute;
use App\Models\AttributeItem;
use App\Models\Category;
use App\Models\Center;
use App\Models\CouponItem;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\Service;
use App\Models\ServiceItem;
use App\Models\StoreRoom;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class CouponItemsController extends Controller
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
    }

    public function createByCouponId($coupon_id)
    {
        $centers=Center::all(); $services = Category::where('parent_id', 2)->get();
        $parents = Category::where('parent_id', 1)->get();
        $category_center_id = 0;
        if(User::checkManager() != null) {
            $center_id = User::checkManager();
            $center = Center::find($center_id);
            if($center->centerable_type == Category::class){
                $category_center_id = $center->centerable_id;
            }
        }

        return view('Admin.site.couponItems.create',compact('centers','coupon_id','category_center_id'
            ,'parents','services'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $couponitemable_id = null ; $couponitemable_type = null;

        if($request->type == 'category'){

            $request->validate(['choose_category_id' => 'required'],['choose_category_id.required' => 'لطفا گروه موردنظر خود را انتخاب کنید']);

            $couponitemable_id[]= $request->choose_category_id;
            $couponitemable_type= Category::class;
        }

        if($request->type == 'service') {
            if (isset($request->services_category_id)) {
                $couponitemable_id[] = $request->services_category_id;$couponitemable_type = $request->services_category_type;
            }

            if (isset($request->service_id)) {
                $couponitemable_id[] = $request->service_id;$couponitemable_type = $request->service_type;
            }

            if (isset($request->service_item_id)) {
                $couponitemable_id[] = $request->service_item_id;$couponitemable_type = $request->service_item_type;
            }

        }

        if($request->type == 'center'){
            $couponitemable_id[] = $request->center_id;
            $couponitemable_type=Center::class;
        }

        if($request->type == 'product'){
            $couponitemable_id=$request->product_attribute_id;
            $couponitemable_type=ProductAttribute::class;
        }

        if($couponitemable_id != null && $couponitemable_type != null){
            foreach ($couponitemable_id as $item) {
                $c_i = CouponItem::where('coupon_id',$request->coupon_id)->where('couponitemable_id',$item)
                    ->where('couponitemable_type',$couponitemable_type)->count();
                if($c_i == 0){
                    CouponItem::create(['coupon_id'=>$request->coupon_id,'couponitemable_id'=>$item,
                        'couponitemable_type'=>$couponitemable_type]);
                }
            }
            return redirect()->back()->with(['create' => 'success']);
        }

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($coupon_id)
    {
        $coupon_items = CouponItem::where('coupon_id',$coupon_id)->paginate(25);
        foreach($coupon_items as $item) {
            if(isset($item->couponitemable->product_id)){
                $item['product_name'] = Product::find($item->couponitemable->product_id)->name;
                $item['value'] = $item->couponitemable->value;
                $item['center_name'] = Center::centerById($item->center_id,'title');
            }
            if(isset($item->couponitemable->id)) $item['category_title'] =$item->couponitemable->title;

        }
        return view('Admin.site.couponItems.lists',compact('coupon_items','coupon_id'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($coupon_item_id)
    {
        $coupon_item = CouponItem::find($coupon_item_id);
        $coupon_id = $coupon_item->coupon_id;
        $coupon_item->delete();
        return redirect(route('couponItem.show',[$coupon_id]))->with([
            'delete' => 'success'
        ]);
    }

    public function findCategoryByProductName(Request $request)
    {
        $center_id=$request->center_id;
        if(isset($center_id) || $request->name != ''){

            if(isset($center_id)){

                $products=Center::find($center_id)->products()->select('products.id', 'products.name', 'products.image'
                    , 'products.root_category_id')->where('products.isShow', 1)->where('products.status', 1)
                    ->orderBy('products.id', 'DESC');
                if($request->name != null){
                    $products=$products->where('products.name', 'like', '%' . $request->name . '%')->get();
                }
                else {$products=$products->get();}

            }
            else{
                $products=Product::select('id', 'name', 'image', 'root_category_id')->where('isShow', 1)->where('status', 5)->
                where('name', 'like', '%' . $request->name . '%')
                    ->orderBy('id', 'DESC')->get();
            }

            if ($products != '') {
                $view = view('Admin.site.partials.coupon.findCategoryByProductName', ['products' => $products]);
                $view_result = $view->render();
            }
            return ['status'=>'true','view_result' => $view_result];
        }
        return ['status'=>'false'];
    }

    public function productAttribute(Request $request)
    {
        if(isset($request->products_ids)){
            if($request->center_id != null){
                $product_attributes = ProductAttribute::where('product_id',$request->products_ids)
                    ->where('price','!=','')->where('productattributeable_id',$request->center_id)->where('productattributeable_type',Center::class)->get();
            }else{
                $product_attributes = ProductAttribute::where('product_id',$request->products_ids)
                    ->where('price','!=','')->get();
            }

            foreach ($product_attributes as $item){
                if($item->productattributeable_type == Center::class){
                    $item['seller_type'] ='مرکز';
                    $item['attribute_title']=Attribute::find($item->attribute_id)->title;
                    $item['seller']=Center::find($item->productattributeable_id)->title;
                }
                if($item->productattributeable_type == ProductStoreRoom::class){
                    $item['seller_type'] ='انبار';
                    $item['attribute_title']=Attribute::find($item->attribute_id)->title;
                    $item['seller']= StoreRoom::storeRoomName($item->productattributeable_id);
                }
            }

            $view = view('Admin.site.partials.coupon.productAttribute', ['attributes' => $product_attributes]);
            $view_result = $view->render();
            return ['status' => 'true', 'view_result' => $view_result];
        }
        return ['status' => 'false'];
    }

    public function findServiceByCategoryId(Request $request)
    {
        if($request->id != ''){
            $services=Service::where('category_id',$request->id)->get();
            return ['status'=>'true','services'=>$services];
        }
        return ['status'=>'false'];
    }

    public function findServiceItem(Request $request)
    {
        if($request->id != ''){
            $services_items=ServiceItem::where('service_id',$request->id)->get();
            return ['status'=>'true','services_items'=>$services_items];
        }
        return ['status'=>'false'];
    }


}
