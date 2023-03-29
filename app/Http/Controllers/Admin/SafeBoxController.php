<?php

namespace App\Http\Controllers\Admin;

use App\Models\SafeBox;
use App\Models\Representation;
use App\Models\Product;
use App\Models\CountingUnit;
use App\Models\CategoryStoreRoom;
use App\Models\ProductStoreRoom;
use App\Models\Category;
use Verta;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;

class SafeBoxController extends Controller
{
    public function index()
    {
        $safe_boxes = SafeBox::all();
        return view('Admin.SafeBox.all', compact('safe_boxes'));
    }

    public function create()
    {
        $representations = Representation::whereStatus(true)->get();
        return view('Admin.storeroom.create' , compact('representations'));
    }

    public function store(Request $request)
    {
        $this->validate(request(), [
            'code' => 'required',
            'address' => 'required',
        ]);
        $store_rooms = StoreRoom::create($request->all());
        //cache
        Cache::flush();

        return redirect(route('storeRoom.index'))->with([
            'create'=>'success'
        ]);
    }

    public function show(Menu $menu)
    {
        //
    }

    public function edit(StoreRoom $storeRoom)
    {
        $representations = Representation::whereStatus(true)->get();
        return view('Admin.storeroom.edit', compact('storeRoom' , 'representations'));
    }
    public function createProduct(StoreRoom $storeRoom)
    {
        $products = Product::where('type' , '!=' , 2)->get();
        $category_store_rooms = CategoryStoreRoom::all();
        $counting_units = CountingUnit::all();
        $parents = Category::where('parent_id', 0)->get();
		$store_room_products = $storeRoom->product_storeRoom;
        return view('Admin.storeroom.product', compact('store_room_products','storeRoom' , 'products' , 'parents', 'category_store_rooms', 'counting_units'));
    }
	 public function storeProduct(Request $request , StoreRoom $storeRoom)
    {
        $input = $request->all();
        $v = Verta::parse(self::numberConverter($input['expire_date']));
        $input['expire_date'] = $v->formatGregorian('Y-m-d H:i:s');
        $v = Verta::parse(self::numberConverter($input['expire_date_alert']));
        $input['expire_date_alert'] = $v->formatGregorian('Y-m-d H:i:s');
        $product_store_room = ProductStoreRoom::create($input);
        return redirect(url('dashboard/create-product-storeRoom' , ['id' => $storeRoom->id ]))->with([
            'purchase_invoice_update' => 'success'
        ]);
    }
    public function update(Request $request, StoreRoom $storeRoom)
    {
        $this->validate(request(), [
            'code' => 'required|unique:store_rooms',
            'address' => 'required',
        ]);

        $inputs = $request->all();
        $storeRoom->update($inputs);

        //cache
        Cache::flush();

        return redirect(route('storeRoom.index'))->with([
            'update' => 'success'
        ]);
    }

    public function destroy(Menu $menu)
    {
        if ($menu->getChild()->get()->count() > 0) {
            foreach ($menu->getChild()->get() as $midmenu) {
                if ($midmenu->getChild()->get()->count() > 0) {
                    foreach ($midmenu->getChild()->get() as $minmenu) {
                        $minmenu->delete();
                    }
                    $midmenu->delete();
                } else {
                    $midmenu->delete();

                }
            }
            $menu->delete();
        } else {
            $menu->delete();
        }

        //cache
        Cache::flush();

        return redirect()->back()->with([
            'delete' => 'success'
        ]);
    }

    public function sort(Request $request)
    {
        if ($request->ajax()) {
            $id = $request->input('id');
            $sort = $request->input('sort');
            Menu::find($id)->update(['sort' => $sort]);

            //cache
            Cache::flush();
        }
    }

    public function numberConverter($number)
    {
        $persian = array('۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹');
        $num = range(0, 9);
        return str_replace($persian, $num, $number);
    }
}
