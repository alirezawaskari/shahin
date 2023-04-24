<?php

namespace App\Http\Controllers\Admin\site;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Resources\ServiceCollection;
use App\Http\Resources\ServiceResource;
use App\Models\AttributeGroup;
use App\Models\Category;
use App\Models\Gallery;
use App\Models\Menu;
use App\Models\Service;
use App\Models\Slider;
use File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function index()
    {
        $categories = Category::where('depth', 0)->orderBy('id', 'DESC')->paginate(15);
        return view('Admin.site.category.lists', compact('categories'));
    }

    /**
     *
     */
    public function create()
    {
        //
    }

    /**
     * Show the form for creating a new category.
     *
     * @return \Illuminate\Http\Response
     */
    public function show($cat_id)
    {
        //دسته بندی مشابه
        $all_categories = Category::select('id', 'title')->where('depth',1)->get();
        //خدمات مشابه
        $all_services = Service::select('id', 'title')->get();
        $parent = Category::find($cat_id);
        $path = '';
        if ($parent->depth == 0) { $path = $parent->id; }
        if ($parent->depth == 1) {
            $father = Category::find($parent->path);
            $path = $father->id . '/' . $parent->id;
        }
        //for depth = 2
        if ($parent->depth > 1)  $path = $parent->path . '/' . $parent->id;
        $path_explode = explode('/',$path);
        $cateDepthOne = Category::where('depth', 1)->where('path', $path_explode[0])->get();

        $depth=$parent->depth + 1;
        $family_cat_ids_list= Category::select('id','title')->where('parent_id', $cat_id)->get();

        return view('Admin.site.category.create', compact('parent','path','all_categories'
                ,'all_services','depth','cateDepthOne','family_cat_ids_list'));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function store(Request $request)
    {
        if($request->depth = 0 || $request->depth = 1){
            $request->validate(['title' => 'required', 'en_title' => 'required',
                'verticalImages.*.file' => 'required',
                'horizontalImages.*.file' => 'required'
            ],
                ['title.required' => 'لطفا عنوان را وارد کنید', 'en_title.required' => 'لطفا عنوان انگلیسی دسته بندی را وارد کنید',
                    'verticalImages.*.file.required'=>'لطفا عکس را وارد کنید',
                    'horizontalImages.*.file.required'=>'لطفا عکس را وارد کنید',
                ]
            );
        }
        else if($request->depth > 1){
            $request->validate(['title' => 'required', 'en_title' => 'required'
            ],
                ['title.required' => 'لطفا عنوان را وارد کنید', 'en_title.required' => 'لطفا عنوان انگلیسی دسته بندی را وارد کنید',]
            );
        }

        if($request->min_credit > 0)$request['min_credit'] = str_replace(",","",$request->min_credit);
        if($request->max_credit > 0)$request['max_credit'] = str_replace(",","",$request->max_credit);
        if($request->price > 0)$request['price'] = str_replace(",","",$request->price);
        $category = new Category();
        $category->title = $request->input('title');
        $category->en_title = $request->input('en_title');
        $category->parent_id = $request->input('parent_id');
        $category->path = $request->input('path');
        $category->depth = $request->input('depth');
        if ($request->input('isActive') != null)
            $category->isActive = $request->input('isActive');
        if ($request->input('displayOrder') != null)
            $category->displayOrder = $request->input('displayOrder');
        $category->save();

        if($request->menu_title != '') $menu_title = $request->menu_title;
        else $menu_title = $category->title;

        if($request->is_menu == 1){
            Menu::create(['menuable_id' => $category->id,'parent_id'=>$request->input('parent_id')
                ,'menuable_type'=>Category::class
                ,'position'=>'1','title'=>$menu_title,'depth'=>$request->input('depth')]);
        }

        if($request->other_parent_title != '')  $other_parent_title = $request->other_parent_title ;
        else $other_parent_title = $category->title ;

        if(isset($request->other_parent_id) && $request->other_parent_id != null){
            Menu::create(['menuable_id' => $category->id ,'menuable_type'=>Category::class,
                'parent_id'=>$request->other_parent_id,'title'=>$other_parent_title,'depth'=>2,'duplicate'=>1]);
        }

        $image_name = $request->image_path;
        if(isset($image_name)) {
            if (\File::exists(public_path($category->image))) {
                \File::delete(public_path($category->image));
            }
                $category->image = Helper::moveImage($image_name,'category');
                $category->save();
            }
        $file_icon = $request->icon_path;
        if(isset($file_icon)) {
            if (\File::exists(public_path($category->icon))) {
                \File::delete(public_path($category->icon));
            }
            $category->icon = Helper::moveImage($file_icon,'category');
            $category->save();
        }

        if (isset($request->similar_categories)) {
            $category->similarGroups()->attach($request->similar_categories, ['similar_model' => Category::class, 'relation_model' => Category::class]);
        }
        if (isset($request->similar_services)) {
            $category->similarGroupsServices()->attach($request->similar_services, ['similar_model' => Category::class, 'relation_model' => Service::class]);
        }


        //banner vertical image
        $files = $request->verticalImages;
        if ($files != '') {
            foreach ($files as $extra_file) {
                if (isset($extra_file['file'])) {
                        Gallery::create(['galleryable_id' => $category->id,
                            'galleryable_type' => Category::class,
                            'title' => $extra_file['file_title'],
                            'image' =>  Helper::moveImage($extra_file['file'],'category'),
                            'type' => 0]);
                }
            }
        }
        //banner horizontal image
        $files = $request->horizontalImages;
        if ($files != '') {
            foreach ($files as $extra_file) {
                if (isset($extra_file['file'])) {
                    Gallery::create(['galleryable_id' => $category->id,
                        'galleryable_type' => Category::class,
                        'title' => $extra_file['file_title'],
                        'image' => Helper::moveImage($extra_file['file'],'category')
                        , 'type' => 1]);
                }
            }
        }

        //Sliders
        $multiple_images = $request->multiple_image;
        if ($multiple_images != '') {
            foreach ($multiple_images as $multiple_image) {
                if (isset($multiple_image['file'])) {
                    Slider::create(['title'=>$multiple_image['title'],'link'=>$multiple_image['link'],
                        'type'=>1,'category_id'=>$category->id , 'img'=> Helper::moveImage($multiple_image['file'],'slider')]);

                }
            }
        }

        //ارتاط دسته بندی ها
        if (isset($request->family_cat_ids_list) && $request->family_cat_ids_list != []) {
            $category->related()->attach($category->id);
            $category->related()->attach($request->family_cat_ids_list);
        }


        if ($category->parent_id == 0) {
            $categories = Category::where('depth', 0)->orderBy('id', 'DESC')->paginate(15);
            return view('Admin.site.category.lists', ['categories' => $categories,'create' => 'success']);
        } else
            return redirect('/dashboard/category/sub/' . $category->parent_id )->with([
                'create' => 'success'
            ]);
    }

    /**
     * @param $id
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application|\Illuminate\Http\RedirectResponse
     */
    public function edit($id)
    {
        if ($id) {
            $category = Category::find($id);
            $parent_id = $category->parent_id;
            //دسته بندی های مشابه
            $all_categories = Category::where('id', '!=', $id)->select('id', 'title')->get();
            //خدمات مشابه
            $all_services = Service::select('id', 'title')->get();
            $parents = Category::where('parent_id', 0)->get();


            //مرتبط با دسته بندی
            $family_cat_ids_list=[];
            if($category->depth > 1) {
                $path = explode('/', $category->path);
                $path = $path[0] . '/' . $path[1];
                $child_of_parent = Category::where('path', 'like', $path)->pluck('id');
            }

            if($category->depth == 2){
                $family_cat_ids_list = Category::where('path', $category->path)->get();
            }

            if($category->depth == 3){
                $family_cat_ids_list= Category::whereIn('parent_id',$child_of_parent->toArray())->get();
            }

            if($category->depth == 4){
                $childs = Category::whereIn('parent_id',$child_of_parent->toArray())->pluck('id');
                $family_cat_ids_list= Category::whereIn('parent_id',$childs->toArray())->get();
            }

            $related_category = $category->related()->pluck('related_id')->toArray() ;

            $similar_categories = $category->similarGroups()->where('similar_model', Category::class)->where('relation_model', Category::class)->pluck('relation_id')->toArray();
            $similar_services = $category->similarGroupsServices()->where('similar_model', Category::class)->where('relation_model', Service::class)->pluck('relation_id')->toArray();

            $hasInOtherMenu=Menu::where('menuable_id',$category->id)->where('menuable_type',Category::class)
                ->where('parent_id','!=',$category->parent_id)->get();

            $cateDepthOne=[];
            //for depth = 2
            if ($category->depth > 1)  {
                $path = $category->path . '/' . $category->id;
                $path_explode = explode('/',$path);
                $cateDepthOne = Category::where('depth', 1)->where('path', $path_explode[0])->get();
            }

            return view('Admin.site.category.edit',
                compact('category','parents','all_categories','all_services','similar_categories','similar_services',
                    'all_services','similar_categories', 'similar_services','cateDepthOne','family_cat_ids_list','related_category','hasInOtherMenu','parent_id')
            );
        }
        return redirect()->back();
    }

    /**
     * @param Request $request
     * @param $id
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application|\Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {

        $request->validate(['title' => 'required', 'en_title' => 'required',
        ],
            ['title.required' => 'لطفا نام دسته بندی را وارد کنید',
                'en_title.required' => 'لطفا نام انگلیسی دسته بندی را وارد کنید',
            ]
        );
        if($request->min_credit > 0)$request['min_credit'] = str_replace(",","",$request->min_credit);
        if($request->max_credit > 0)$request['max_credit'] = str_replace(",","",$request->max_credit);
        if($request->price > 0)$request['price'] = str_replace(",","",$request->price);
        $request['isActive'] = isset($request['isActive']) ? $request['isActive'] : 0;
        $request['displayOrder'] = isset($request['displayOrder']) ? $request['displayOrder'] : 0;

        $cat = Category::find($id);

        $cat->update($request->all());

        if($request->menu_title != '') $menu_title = $request->menu_title;
        else $menu_title = $cat->title;

        if($request->is_menu == 1){
            Menu::updateOrCreate(['menuable_id' => $id , 'menuable_type'=>Category::class],[
                'menuable_type' => Category::class,'parent_id'=>$cat->parent_id,'title'=>$menu_title
                ,'depth'=>$cat->depth]);
        }
        if($request->is_menu == 0){
           Menu::where('menuable_id',$id)->where('menuable_type',Category::class)->delete();
        }

        if($request->other_parent_title != '')  $other_parent_title = $request->other_parent_title;
        else $other_parent_title = $cat->title;

        if($request->other_parent_id){
            Menu::create(['menuable_id' => $id,
                'menuable_type' => Category::class,'parent_id'=>$request->other_parent_id,
                'title'=>$other_parent_title,'depth'=>2,'duplicate'=>1,'duplicate_subset'=>$request->duplicate_subset]);
        }

        $image_name = $request->image_path;
        if(isset($image_name)) {
            $cat->image = Helper::moveImage($image_name,'category');
            $cat->save();
        }

        $file_icon = $request->icon_path;
        if(isset($file_icon)) {
            $cat->icon = Helper::moveImage($file_icon,'category');
            $cat->save();
        }

        //banner : vertical images
        $files = $request->verticalImages;
        if ($files != '') {
            foreach ($files as $extra_file) {
                if (isset($extra_file['file'])) {
                    if (isset($extra_file['gallery_id'])) {

                        $gallery = Gallery::find($extra_file['gallery_id']);
                        if ($gallery) {
                            $image = Helper::moveImage($extra_file['file'],'category');
                            $gallery->update(['title' => $extra_file['file_title'], 'image' => $image]);
                        }
                    }
                    else{
                        $image = Helper::moveImage($extra_file['file'],'category');
                        Gallery::create(['galleryable_id' => $cat->id,
                            'galleryable_type' => Category::class, 'title' => $extra_file['file_title'],
                            'image' => $image, 'type' => 0]);
                    }

                }
            }
        }

        //banner : horizontal images
        $files = $request->horizontalImages;
        if ($files != '') {
            foreach ($files as $extra_file) {
                if (isset($extra_file['file'])) {
                    if (isset($extra_file['gallery_id'])) {
                        $gallery = Gallery::find($extra_file['gallery_id']);
                        if ($gallery) {
                            $image = Helper::moveImage($extra_file['file'],'category');
                            $gallery->update(['title' => $extra_file['file_title'], 'image' => $image]);
                        }
                    }
                    else{
                        $image = Helper::moveImage($extra_file['file'],'category');
                        Gallery::create(['galleryable_id' => $cat->id,
                            'galleryable_type' => Category::class, 'title' => $extra_file['file_title'],
                            'image' => $image, 'type' => 1]);
                    }
                }
            }
        }


        if (isset($request->similar_categories) && $request->similar_categories != []) {
            $cat->similarGroups()->where('relation_model', Category::class)->sync($request->similar_categories);
        }

        if (isset($request->similar_services) && $request->similar_services != []) {
            $cat->similarGroupsServices()->where('relation_model', service::class)->sync($request->similar_services);
        }

        $cat->related()->sync($request->family_cat_ids_list,$cat->id);


        $files = $request->multiple_image;
        if ($files != '') {
            foreach ($files as $extra_file) {
                if (isset($extra_file['file'])) {
                    $image = Helper::moveImage($extra_file['file'],'service');
                    Slider::create(['title'=>$extra_file['title'],'link'=>$extra_file['link'],
                        'type'=>1,'category_id'=>$cat->id , 'img'=> $image]);

                }
            }
        }

        if(isset($request->edit_slider)){
            $edit_sliders = $request->edit_slider;
            foreach ($edit_sliders as $index=>$edit_slider) {
                $slider=Slider::find($index);
                $slider->update(['title'=>$edit_slider['title'],'link'=>$edit_slider['link']]);
            }
        }

        if(isset($request->category_id) && $request->category_id[0] != null){
            $category_id=$request->category_id;$path='';
            $depth = count($category_id) - 1;
            for($i=0 ; $i < count($category_id) - 1 ; $i++){
                if($i < count($category_id) - 2){
                    $path=$path . $category_id[$i] .'/';
                }else{
                    $path=$path . $category_id[$i] ;
                }
            }

            $cat->parent_id = $category_id[3];
            $cat->depth = $depth;
            $cat->path = $path;
            $cat->save();
        }

        if ($cat->parent_id == 0) {
            $categories = Category::where('depth', 0)->orderBy('id', 'DESC')->paginate(15);
            return view('Admin.site.category.lists', ['categories' => $categories,'update' => 'success']);
        }
        else

        return redirect()->back()->with([ 'update' => 'success']);
//        return redirect('/dashboard/category/sub/' . $cat->parent_id )->with([ 'update' => 'success']);

    }

    /**
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        if($id){
            $category=Category::find($id);
            if($category) {
                $category->delete();
            }
        }
        return redirect()->back();
    }

    /**
     * Display a listing of the sub Category.
     * @return \Illuminate\Http\Response
     */
    public function subCategory(Request $request,$category_id)
    {
        $selected_cat = Category::find($category_id);
        if(isset($selected_cat) && $selected_cat != null){
            $path = '';
            if ($selected_cat->depth == 0) {
                    $path = $selected_cat->id;
                }
            if ($selected_cat->depth == 1) {
                    $father = Category::find($selected_cat->path);
                    $path = $father->id . '/' . $selected_cat->id;
                }
            $categories = Category::where('parent_id', $category_id)->orderBy('id', 'DESC');
            $columns = ['id', 'title'];
            $queries = [];
            foreach ($columns as $column) {
                    if ($request->has($column)) {
                        if ($request->$column != null) {
                            if ($column == 'id') {
                                $categories = $categories->where($column, $request->$column);
                                $queries[$column] = $request->$column;
                            }
                            if ($column == 'title') {
                                $categories = $categories->where($column, 'like', '%' . $request->$column . '%');
                                $queries[$column] = $request->$column;
                            }
                        }
                    }
                }
            $categories = $categories->paginate(15);
            return view('Admin.site.category.subCategory', ['categories' => $categories,
                 'selected_cat' => $selected_cat, 'path' => $path,'queries'=>$queries]);
        }
        return redirect()->back()
            ->with(['failByMessage' => 'دسته بندی مورد نظر یافت نشد']);
    }

    //Find a subset of a category

    /**
     * @param Request $request
     * @return array|string[]
     */
    public function findChild(Request $request)
    {
        if (isset($request->id)) {
            $brands=[]; $view_result='';$depth=null;
            $categories = Category::select('id', 'title')->where('parent_id', $request->id)->get();
            $cat = Category::select('id', 'title','depth')->where('parent_id', $request->id)->first();
            if($cat)$depth = $cat->depth ;
            $hasChild = Category::hasChild($request->id);

            if($hasChild == 0){
                $brands =  Category::find($request->id)->brands;

                $attributeGroups = AttributeGroup::where('category_id', $request->id)->get();
                if(isset($attributeGroups) && count($attributeGroups) > 0){
                    $attributes = [];
                    foreach ($attributeGroups as $item) {
                        $attributes[$item->id] = $item->attributes()->where('is_price',0)->get();
                    }
                    $view = view('Admin.modal.createAttributeGoodModal',['attributes' => $attributes]);
                    $view_result=$view->render();
                }
            }

            return ['categories' => $categories,'view_result'=>$view_result,'brands'=>$brands,'has_child'=>$hasChild
            ,'depth'=>$depth];
        }
        return ['status' =>'false'];
    }

    //Use in menu by category

    /**
     * @param Request $request
     * @return string[]
     */
    public function changePosition(Request $request)
    {
        $position = $request->position;
        if(isset($position)){
            $i=1;
            foreach($position as $pos){
               Menu::updateOrCreate(['id' => $pos], ['position'=>$i]);
                $i++;
            }
            return ['status'=>'true'];
        }
        return ['status'=>'false'];
    }

    //Use in menu by category

    /**
     * @param $id
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function categoryPosition($id)
    {
        $menu = Menu::where('parent_id',$id)->where('menuable_type', Category::class)
            ->orderBy('position','ASC')->get();
        return view('Admin.site.category.categoryPosition', ['menu' => $menu,'selected_menu'=>$id]);
    }

    //remove Category in Menu table

    /**
     * @param Request $request
     * @return string[]
     */
    public function removeCatMenu(Request $request)
    {
       if(isset($request->id)){
           $menu=Menu::find($request->id);
           if($menu){$menu->delete();
               return ['status'=>'true'];
           }
       }
       return ['status'=>'false'];
    }

    //search Category use in Store Good and show Category in AttributeGroup

    /**
     * @param Request $request
     * @return array
     */
    public function searchCategory(Request $request)
    {
        $service_cat = Category::where('id',2)->select('id','title')->first();
        $categories = Category::select('id','title')->where('isActive',1)
            ->where('id','!=',2)->where('parent_id','!=',$service_cat->id)
            ->where('title','like','%'. $request->keyword .'%')->get();

        foreach ($categories as $ind=>$category) {
            if(Category::countChild($category->id) == 0){
                $category['parents'] = Category::findFather($category->id,$request->category_center_id);
            }
       }

        $view = view('Admin.site.partials.searchCategory', ['categories' => $categories]);
        $view_result = $view->render();
        return ['view_result' => $view_result];
    }

    //after choose category : display brand and Attribute

    /**
     * @param Request $request
     * @return array|null
     */
    public function findBrand(Request $request)
    {
        $last_category_id=$request->id;
        if($last_category_id){
            $view_result='';$brand_view_result='';
            $hasChild = Category::hasChild($last_category_id);
            if($hasChild == 0){
                $attributeGroups = AttributeGroup::where('category_id', $last_category_id)->get();
                if(isset($attributeGroups) && count($attributeGroups) > 0){
                    $attributes = [];
                    foreach ($attributeGroups as $item) {
                        $attributes[$item->id] = $item->attributes()->where('is_price',0)->get();
                    }
                    $view = view('Admin.modal.createAttributeGoodModal',['attributes' => $attributes]);
                    $view_result=$view->render();
                }

                $brands =  Category::find($last_category_id)->brands;
                if($brands != null){
                    $brand_view = view('Admin.site.brand.brandCategory',['brands' => $brands,'category_id'=>$last_category_id]);
                    $brand_view_result = $brand_view->render();
                }
            }
            return ['brand_view_result'=>$brand_view_result,'view_result'=>$view_result];
        }
        return null;
    }

    /**
     * fetch services of category
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Foundation\Application|\Illuminate\Http\Response
     */
    public function fetchServicesByCategory(Request $request)
    {
        $validator = Validator::make($request->all(), ['id'=>['required',Rule::exists('categories','id')]]);
        if ($validator->fails()) {
            return response(['status'=>'error','messages'=>$validator->messages()->all()]);
        } else {
            $category=Category::find($request->id);
            return response(['status'=>'success','data'=>   new ServiceCollection($category->services)]);
        }
    }



}
