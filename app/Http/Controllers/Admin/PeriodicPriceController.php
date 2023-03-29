<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Center;
use App\Models\Coupon;
use App\Models\CouponItem;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\User;
use Carbon\Carbon;
use Hekmatinasser\Verta\Verta;
use Illuminate\Http\Request;
use Morilog\Jalali\CalendarUtils;

class PeriodicPriceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $center_id = null;
        if (User::checkManager() != null) {
            $center_id = User::checkManager();
            $center = Center::find($center_id);
        }
        if ($center_id != null) {
            $coupon_item = CouponItem::where('center_id', $center_id)
                ->where('couponitemable_type', ProductAttribute::class);
            $product_attribute_ids = $coupon_item->pluck('couponitemable_id');

            if ($product_attribute_ids) {
                $product_attributes = ProductAttribute::whereIn('id', $product_attribute_ids)
                    ->where('productattributeable_id', $center_id)
                    ->where('productattributeable_type', Center::class)
                    ->where('price', '!=', null)->orderBy('id', 'DESC');

                $all_items = $request->all_items;
                $search_term = $request->search_term;

                $queries['all_items'] = $all_items;
                $queries['search_term'] = $search_term;

                if ($search_term != null) {
                    if ($search_term != null && $all_items == 'product_name') {
                        $product_attributes->with('product')->whereHas('product', function ($query) use ($search_term) {
                            $query->where('name', 'like', '%' . $search_term . '%');
                        });
                    } else {
                        $product_attributes = $product_attributes->where($all_items, $search_term);
                    }
                }

                $product_attributes = $product_attributes->paginate(15)->appends($queries);
                foreach ($product_attributes as $product_attribute) {
                    $product_attribute['guarantee'] = ProductAttribute::GUARANTEE[$product_attribute->guarantee];
                    $price = $product_attribute->price;
                    $couponItems = $product_attribute->couponItems->first();
                    $coupon = $couponItems->coupon;

                    $amount_discount = $price * $coupon->discount / 100;
                    $product_attribute['price_by_discount'] = $price - $amount_discount;
                    $product_attribute['purchase_type'] = $coupon->purchase_type;
                    $product_attribute['pay_type'] = $coupon->pay_type;
                    $product_attribute['capacity'] = $coupon->capacity;

                    $product_attribute['start_at'] = $coupon->start_at;
                    $product_attribute['end_at'] = $coupon->end_at;
                    $product_attribute['discount'] = $coupon->discount;
                    $product_attribute['coupon_id'] = $coupon->id;
                    if ($coupon->status == 1) $status = 'فعال'; else $status = 'غیرفعال';
                    $product_attribute['status_title'] = $status;
                    //وقتی که از زمان شروع تخفیفی گذشته باشد نمی توان تخفیف را ویرایش کرد
                    $product_attribute['able_to_edit'] = CouponItem::hasCouponByProductAttributeId($product_attribute->id);

                    $product = $product_attribute->product;
                    if ($product->brand_id) {
                        $brand = Brand::find($product->brand_id);
                        if (isset($brand)) {
                            $product_attribute['brand_title'] = $brand->title;
                            $product_attribute['brand_title_en'] = Brand::find($product->brand_id)->title_en;
                        }
                    }

                    $latestCategory = Category::latestCategory($product);
                    $category = Category::find($latestCategory);
                    $product_attribute['minimum_discount'] = $category->minimum_discount;
                }

                $type_id = 1;

                return view("Admin.product.price.smartDiscounts.index", compact('queries', 'center_id',
                    'product_attributes', 'center', 'type_id'));
            }
        }

        return view("Admin.product.price.smartDiscounts.index");
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        if (User::checkManager() != null) {
            $center_id = User::checkManager();
            $product_ids = Product::with('centers')->whereHas('centers', function ($query) use ($center_id) {
                $query->where('center_id', $center_id);
            })->select('id', 'name', 'image', 'type', 'isShow', 'root_category_id', 'internal_use', 'brand_id', 'status')
                ->orderBy('id', 'DESC')->where('type', 1)->pluck('id');

            $coupon_item = CouponItem::where('center_id', $center_id)->where('couponitemable_type', ProductAttribute::class);

            $product_attribute_ids = $coupon_item->pluck('couponitemable_id');

            $product_attributes = ProductAttribute::whereIn('product_id', $product_ids)
                ->where('productattributeable_id', $center_id)
                ->where('productattributeable_type', Center::class)
                ->where('price', '!=', null)
                ->whereNotIn('id', $product_attribute_ids->toArray())
                ->orderBy('id', 'DESC');


            foreach ($product_attributes as $product_attribute) {

                $product_attribute['guarantee'] = ProductAttribute::GUARANTEE[$product_attribute->guarantee];
                $product = $product_attribute->product;
                if ($product->root_category_id) {
                    $product_attribute['category_title'] = Category::findCategory($product->root_category_id, 'title');
                }
            }

            $queries = [];
            $all_items = $request->all_items;
            $search_term = $request->search_term;

            $queries['all_items'] = $all_items;
            $queries['search_term'] = $search_term;

            if ($search_term != null) {
                if ($search_term != null && $all_items == 'product_name') {
                    $product_attributes->with('product')->whereHas('product', function ($query) use ($search_term) {
                        $query->where('name', 'like', '%' . $search_term . '%');
                    });
                } else {
                    $product_attributes = $product_attributes->where($all_items, $search_term);
                }
            }

            $product_attributes = $product_attributes->paginate(15)->appends($queries);


            if (isset($request->page)) {
                return view('Admin.product.price.smartDiscounts.productsAttributeList', ['product_attributes' => $product_attributes])->render();
            }

            return view('Admin.product.price.smartDiscounts.create', ['product_attributes' => $product_attributes]);
        }
        return redirect('/dashboard/inaccessibility');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    //store in session
    public function store(Request $request)
    {
        $request->session()->forget('product_attribute_id');
        session(['product_attribute_id' => $request->product_attribute_id]);
        return redirect('/dashboard/periodicPricesProduct/completeSelection');
    }

    public function completeSelection(Request $request)
    {
        $product_attribute_ids = session('product_attribute_id');

        if ($product_attribute_ids) {
            if (User::checkManager() != null) {
                $center_id = User::checkManager();
                $center = Center::find($center_id);
                $product_attributes = ProductAttribute::whereIn('id', $product_attribute_ids)
                    ->where('productattributeable_id', $center_id)
                    ->where('productattributeable_type', Center::class)
                    ->where('price', '!=', null)->orderBy('id', 'DESC');

                $all_items = $request->all_items;
                $search_term = $request->search_term;

                $queries['all_items'] = $all_items;
                $queries['search_term'] = $search_term;

                if ($search_term != null) {
                    if ($search_term != null && $all_items == 'product_name') {
                        $product_attributes->with('product')->whereHas('product', function ($query) use ($search_term) {
                            $query->where('name', 'like', '%' . $search_term . '%');
                        });
                    } else {
                        $product_attributes = $product_attributes->where($all_items, $search_term);
                    }
                }

                $product_attributes = $product_attributes->paginate(15)->appends($queries);

                foreach ($product_attributes as $product_attribute) {
                    $product_attribute['guarantee'] = ProductAttribute::GUARANTEE[$product_attribute->guarantee];
                    $product = $product_attribute->product;
                    if ($product->brand_id) {
                        $brand = Brand::find($product->brand_id);
                        if (isset($brand)) {
                            $product_attribute['brand_title'] = $brand->title;
                            $product_attribute['brand_title_en'] = Brand::find($product->brand_id)->title_en;
                        }
                    }
                    $latestCategory = Category::latestCategory($product);
                    $category = Category::find($latestCategory);
                    $product_attribute['minimum_discount'] = $category->minimum_discount;
                }

                return view(".Admin.product.price.smartDiscounts.completeSelection",
                    ['queries' => $queries, 'center_id' => $center_id, 'product_attributes' => $product_attributes
                        , 'type_id' => 1, 'center' => $center]);
            }
        }

        return view("Admin.product.price.smartDiscounts.completeSelection");
    }

    //store in coupon and coupon Item
    public function storeProductIds(Request $request)
    {
        $center_id = null;
        if (User::checkManager() != null) {
            $center_id = User::checkManager();
        }
        $endBiggerStart = false;
        $id = 0;
        $endStartBiggerNow = false;
        foreach ($request->periodic as $ind => $item) {
            $request->validate([
                'periodic.*.discount' => 'required',
                'periodic.*.start_date' => 'required',
                'periodic.*.end_date' => 'required',
                'periodic.*.capacity' => 'required',
                'periodic.*.pay_type' => 'required',
                'periodic.*.purchase_type' => 'required',
            ],
                [
                    'periodic.*.discount.required' => 'لطفا تخفیف را وارد کنید',
                    'periodic.*.start_date.required' => 'لطفا تاریخ شروع را وارد کنید',
                    'periodic.*.start_date.same' => 'نباید تاریخ شروع و پایان یکسان باشد',
                    'periodic.*.end_date.required' => 'لطفا تاریخ پایان را وارد کنید',
                    'periodic.*.end_date.same' => 'نباید تاریخ شروع و پایان یکسان باشد',
                    'periodic.*.capacity.required' => 'لطفا تعداد در تخفیف را وارد کنید',
                    'periodic.*.pay_type.required' => 'لطفا نوع خرید را وارد کنید',
                    'periodic.*.purchase_type.required' => 'لطفا نوع پرداخت را وارد کنید',
                ]);

            $start_date = Helper::convert($item['start_date']);
            $end_date = Helper::convert($item['end_date']);
            $v_start_date = Verta::parse( $start_date );
            $v_end_date = Verta::parse( $end_date );
            $start_date = $v_start_date->formatGregorian('Y-m-d');
            $end_date = $v_end_date->formatGregorian('Y-m-d');

            $currentDate = date('Y-m-d');
            $currentDate = date('Y-m-d', strtotime($currentDate));

            $start_date = date('Y-m-d', strtotime($start_date));
            $end_date = date('Y-m-d', strtotime($end_date));

            if (($currentDate < $start_date) && ($currentDate < $end_date)) {
                if ($start_date > $end_date) {
                    $id = $ind;
                    $endBiggerStart = true;
                }
                else {
                    $product_attribute = ProductAttribute::find($ind);
                    $discount = $item['discount'];
                    $capacity = $item['capacity'];
                    $pay_type = $item['pay_type'];
                    $purchase_type = $item['purchase_type'];

                    $coupon = Coupon::create(['discount' => $discount, 'capacity' => $capacity, 'status' => 1,
                        'start_at' => $start_date, 'end_at' => $end_date, 'pay_type' => $pay_type, 'purchase_type' => $purchase_type]);

                    $couponitemable_type = ProductAttribute::class;

                    $c_i = CouponItem::where('coupon_id', $coupon->id)->where('couponitemable_id', $product_attribute->id)
                        ->where('couponitemable_type', $couponitemable_type)->count();
                    if ($c_i == 0 && $center_id != null) {
                        CouponItem::create(['coupon_id' => $coupon->id, 'couponitemable_id' => $product_attribute->id,
                            'couponitemable_type' => $couponitemable_type, 'center_id' => $center_id]);
                    }
                }
            }
            else {
                $id = $ind;
                $endStartBiggerNow = true;
            }
        }

        if ($endStartBiggerNow == 'true') {
            return redirect()->back()->with(["failByMessage" => ' تاریخ شروع و تاریخ پایان بایستی بزرگتر از تاریخ اکنون باشد']);
        }

        if ($endBiggerStart == 'true') {
            return redirect()->back()->with(["failByMessage" => 'تاریخ پایان بایستی بزرگتر از تاریخ شروع انتخاب نمایید']);
        } else {
            $request->session()->forget('product_attribute_id');
            if ($center_id != null) {
                $product_attribute_ids = ProductAttribute::where('productattributeable_id', $center_id)
                    ->where('productattributeable_type', Center::class)->pluck('id');

                $coupon_count = CouponItem::whereIn('couponitemable_id', $product_attribute_ids)->where('center_id', $center_id)->count();
            }
            return view('Admin.product.price.smartDiscounts.finish', compact('coupon_count'));
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
        //
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
        $start_date = Helper::convert($request->start_date);
        $end_date = Helper::convert($request->end_date);
        $v_start_date = Verta::parse( $start_date );
        $v_end_date = Verta::parse( $end_date );
        $start_date = $v_start_date->formatGregorian('Y-m-d');
        $end_date = $v_end_date->formatGregorian('Y-m-d');


        $product_attribute = ProductAttribute::find($request->p_a_id);
        if ($product_attribute) {
            $coupon_item = $product_attribute->couponItems->first();
            if ($coupon_item && $coupon_item->coupon) {
                $coupon_item->coupon->purchase_type = $request->purchase_type;
                $coupon_item->coupon->pay_type = $request->pay_type;
                $coupon_item->coupon->capacity = $request->capacity;
                $coupon_item->coupon->start_at = $start_date;
                $coupon_item->coupon->end_at = $end_date;
                $coupon_item->coupon->discount = $request->discount;
                $coupon_item->coupon->save();
                return response(['status' => true]);
            }
        }
        return response(['status' => false]);
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


    //remove of session
    public function removeProductAttribute(Request $request)
    {
        $product_attribute_ids = session('product_attribute_id');
        if (isset($request->id)) {
            $product_attribute_id = $request->id;
            $key = array_search($product_attribute_id, $product_attribute_ids);
            unset($product_attribute_ids[$key]);
            session(['product_attribute_id' => $product_attribute_ids]);
            return ['status' => 'true'];
        }
        return ['status' => 'false'];
    }

    public function deleteProductAttribute(Request $request)
    {
        if (isset($request->id)) {
            $coupon = Coupon::find($request->coupon_id);
            $coupon_item = $coupon->couponItems()->where('couponitemable_id', $request->id)
                ->where('couponitemable_type', ProductAttribute::class)->first();
            if ($coupon_item) {
                $coupon_item->delete();
                return ['status' => 'true'];
            }
        }
        return ['status' => 'false'];
    }

    public function checkDate(Request $request)
    {
        if (isset($request->id) && isset($request->start_date) && isset($request->end_date)) {
            $start_date = Helper::convert($request->start_date);
            $end_date = Helper::convert($request->end_date);
            $start_date = CalendarUtils::createCarbonFromFormat('Y-m-d', $start_date)->format('Y-m-d');
            $end_date = CalendarUtils::createCarbonFromFormat('Y-m-d', $end_date)->format('Y-m-d');
            $currentDate = date('Y-m-d');
            $currentDate = date('Y-m-d', strtotime($currentDate));

            $start_date = date('Y-m-d', strtotime($start_date));
            $end_date = date('Y-m-d', strtotime($end_date));

            if (($currentDate < $start_date) && ($currentDate < $end_date)) {
                if ($start_date > $end_date) {
                    return ['message' => 'تاریخ پایان بایستی بزرگتر از تاریخ شروع انتخاب نمایید', 'id' => $request->id, 'status' => 400];
                }
                if ($start_date == $end_date) {
                    return ['message' => 'تاریخ شروع و پایان نمی تواند یکسان باشد.', 'id' => $request->id, 'status' => 400];
                } else {
                    return ['message' => 'اطلاعات با موفقیت ارسال شد.', 'id' => $request->id, 'status' => 200];
                }
            } else {
                return ['message' => 'تاریخ شروع بایستی از تاریخ اکنون بزرگتر باشد', 'id' => $request->id, 'status' => 400];
            }

        }
        return ['message' => 'خطا در ارسال اطلاعات', 'status' => 400];
    }


}
