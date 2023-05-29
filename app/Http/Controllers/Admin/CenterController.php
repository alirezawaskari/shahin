<?php

namespace App\Http\Controllers\Admin;

use App\Models\AttributeGroup;
use App\Models\Category;
use App\Models\Center;
use App\Models\CenterRequest;
use App\Models\Product;
use App\Models\Review;
use App\Models\Service;
use App\Models\Tag;
use App\Models\User;
use App\Models\Validation;
use Cviebrock\EloquentSluggable\Services\SlugService;
use Illuminate\Http\Request;

class CenterController extends AdminController
{
    public function index() {
        if(auth()->user()->isAbleTo('center'))
        {
            $centers = Center::all();
            if ( auth()->user()->center_id )
            {
                $centers = Center::find( auth()->user()->center_id );
            }

            return view( 'Admin.center.all', compact( 'centers' ) );
        }
        return abort('401');
    }

    public function create() {
        return view( 'Admin.center.create');
    }

    public function store( Request $request ) {
        $this->validate( request(), [
            'title' => 'required|string',
            'address' => 'required',
        ] );

        //create centerRequest
        $item = $request->item;
        if ( $request->cat_type == 'category' ) {
            $center_category_id = $item['product_category'];
            $centerable_id = $request->category;
            $centerable_type = Category::class;
        } else {
            $center_category_id = $item['service_category'];
            $centerable_id = $request->service;
            $centerable_type = Service::class;
        }

        $item['center_category_id'] = $center_category_id;
        unset($item['product_category']);
        unset($item['service_category']);

        $center_request = CenterRequest::create($item);
        //create center
        $this->createCenter($centerable_id, $centerable_type, $request, $center_request);
        //create user

        //create task (callCenter)

        return redirect( route( 'center.index' ) )->with( [
            'create' => 'success',
        ] );
    }

    public function show( Center $center ) {
        //
    }

    public function edit( Center $center ) {
        $center_request = $center->center_request;
        return view( 'Admin.center.edit', compact( 'center' , 'center_request' ) );
    }

    public function update( Request $request, Center $center ) {
        $item = $request->item;
        if ( $request->cat_type == 'category' ) {
            $center_category_id = $item['product_category'];
            $centerable_id = $request->category;
            $centerable_type = Category::class;
        } else {
            $center_category_id = $item['service_category'];
            $centerable_id = $request->service;
            $centerable_type = Service::class;
        }

        $item['center_category_id'] = $center_category_id;
        unset($item['product_category']);
        unset($item['service_category']);

        $center->center_request->update($item);

        $center->update( [
            'centerable_id' => $centerable_id,
            'centerable_type' => $centerable_type,
            'title' => $request->title,
            'status' => $request->status,
            'description' => $request->description,
            'address' => $request->address,
        ] );

        return redirect( route( 'center.index' ) )->with( [
            'update' => 'success',
        ] );
    }

    public function serviceTariffs(Center $center ) {
        $service_tariffs = $center->serviceTariffs;
        $tariffs = $center->centerable->tariffs;

        return view( 'Admin.center.serviceTariffList', compact( 'service_tariffs' , 'center','tariffs') );
    }

    public function createServiceTariff(Center $center)
    {
        $tariffs = $center->centerable->tariffs;

        return view('Admin.center.serviceTariffCreate' , compact(['center' , 'tariffs']));
    }

    public function filter()
    {
        $centers = Center::filter()->latest();

        $provinces = Center::all();
        foreach ($provinces as $province) {
            $count = Center::where('province', 'LIKE', '%' . $province->name . '%')->count();
            $province->code = $count;
        }

        $centers = $centers->get();
        return view('Admin.center.all', compact('centers', 'provinces'));
    }

    public function destroy(Center $center)
    {
        $center->delete();
        return redirect()->back()->with([
            'delete' => 'success'
        ]);
    }

    public function doctorsByCenter($center_id)
    {
        $users = User::with('roles')->whereHas('roles', function ($query) {
            $query->where('roles.name', 'doctor');
        })->where('level', 'admin')->where('center_id', $center_id)->paginate(15);
        return view('Admin.center.doctorsByCenter', ['users' => $users]);
    }


    //list of product centers
    public function productsByCenterId($id, Request $request)
    {
        $products = Product::with('centers')->whereHas('centers', function ($query) use ($id) {
            $query->where('center_id', $id);
        })->select('id', 'name', 'image', 'type', 'isShow', 'root_category_id', 'internal_use', 'brand_id', 'status')
            ->orderBy('id', 'DESC')->where('type', 1);

        $categories = Category::categoryDepth($id);


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

        foreach ($product_brands as $item) {
            if (isset($item->brand->title)) $brands[$item->brand->id] = $item->brand->title;
        }

        $brands = array_unique($brands);

        if ($request->ajax()) {
            return view('Admin.center.products.load', ['products' => $products, 'type_id' => 1])->render();
        }
        $page_link = route('productsByCenterId', $id);
        $create_link = route('createGoodByValues', ['type' => 'center', 'id' => $id]);

        return view('Admin.center.products.good', ['products' => $products, 'type_id' => 1
            , 'categories' => $categories, 'queries' => $queries, 'center_id' => $id, 'brands' => $brands,
           'create_link' => $create_link, 'page_link' => $page_link]);
    }

    public function productEdit($id, $center_id)
    {
        $product = Product::find($id);

        $center = Center::find($center_id);
        $category_center_id = $center->centerable_id;
        $category_center = Category::where('parent_id', $category_center_id)->get();
        $category = Category::find($category_center_id);
        if ($category) {
            $parent_category = Category::where('id', $category->parent_id)->select('id')->first();
            $parent_category_id = $parent_category->id;
        }

        $reviews_strength = $product->reviews()->where('reviewable_type', Center::class)
            ->where('reviewable_id', $center_id)->where('type', 0)->get();
        $reviews_weakness = $product->reviews()->where('reviewable_type', Center::class)
            ->where('reviewable_id', $center_id)->where('type', 1)->get();
        $categories_product = $product->categories()->orderBy('category_product.id', 'ASC')->get();


        $parents = Category::where('parent_id', 1)->get();
        $tags = Tag::pluck('name');
        $centers = Center::select('id', 'title')
            ->where('centerable_type', Category::class)->get();
        $product_centers_ids = $product->centers()->pluck('centers.id')->toArray();

        $last_category_id = Category::latestCategory($product);

        $attributeGroups = AttributeGroup::where('category_id', $last_category_id)->get();
        $attributes = [];
        if (isset($attributeGroups) && count($attributeGroups) > 0) {
            foreach ($attributeGroups as $item) {
                $attributes[$item->id] = $item->attributes()->where('is_price', 0)->get();
            }
        }
        $product_attributes = $product->attributes()
            ->where('productattributeable_id', $center_id)->where('productattributeable_type', Center::class)
            ->where('is_price', 0)->get();
        $brands = Category::find($last_category_id)->brands;
        $product_validation = $product->productValidations;
        $validations = Validation::all();

        return view('Admin.center.products.edit', compact('reviews_strength',
            'reviews_weakness', 'categories_product', 'parents', 'tags', 'product', 'center_id', 'centers',
            'parent_category_id', 'category_center_id', 'category_center', 'product_centers_ids',
            'product_attributes', 'brands', 'product_validation', 'validations', 'attributes'));
    }

    public function productUpdate(Request $request, $id)
    {
        $request->validate(['good.name' => 'required'], ['good.name.required' => 'لطفا عنوان را وارد کنید']);
        $product = Product::find($id);
        //user is not store manager
        if (User::checkManager() == null) {
            $request['reference_price'] = str_replace(',', '', $request->reference_price);
            $request['isShow'] = isset($request['isShow']) ? $request['isShow'] : 0;
            $request['is_sale'] = isset($request['is_sale']) ? $request['is_sale'] : 0;
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

        if (isset($request->center_id)) {
            $productattributeable_id = $request->center_id;
            $productattributeable_type = Center::class;
        }

        Product::saveImageGood($request, $product);
        Product::updateAttributeGood($request, $product, $productattributeable_id, $productattributeable_type);

        if ($request->good['name'] != null)
            $request['slug'] = SlugService::createSlug(Product::class, 'slug', $request->good['name']);

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

        //tag
//        if (isset($request->tag)) {
//            $request_tags = $request->tag;
//            $list_tags_product = $product->tagNames();
//
//            Product::UpdateTag($list_tags_product, $request_tags, $product);
//        }

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

        return redirect(route('productsByCenterId',$request->center_id))->with(['update' => 'success']);
    }

    public function getCenterCategory()
    {
        $centers = Center::select('id', 'title')->where('title', '!=', null)
            ->where('centerable_type', Category::class)->get();
        return ['centers' => $centers];
    }

    /**
     * @param mixed $centerable_id
     * @param string $centerable_type
     * @param Request $request
     * @param $center_request
     * @return void
     */
    public function createCenter(mixed $centerable_id, string $centerable_type, Request $request, $center_request): void
    {
        Center::create([
            'centerable_id' => $centerable_id,
            'centerable_type' => $centerable_type,
            'status' => $request->status,
            'representation_id' => auth()->user()->admin_representation_id,
            'center_request_id' => $center_request->id,
            'title' => $request->title,
            'description' => $request->description,
            'address' => $request->address,
        ]);
    }

}
