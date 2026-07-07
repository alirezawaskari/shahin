<?php

namespace App\Http\Controllers\Admin\site;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\AttributeGroup;
use App\Models\Category;
use App\Models\Gallery;
use App\Models\Menu;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class CategoryController extends Controller
{
    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function index()
    {
        $ghorfe = ghorfe();
        if(!$ghorfe || $ghorfe->type_work === 'all')
            $categories = Category::where('depth', 0)->orderBy('id', 'DESC')->paginate(15);
        if($ghorfe && $ghorfe->type_work === 'product')
            $categories = Category::where('id',1)->paginate(15);
        if($ghorfe && $ghorfe->type_work === 'service')
            $categories = Category::where('id',2)->paginate(15);
        return view('Admin.site.category.lists', compact('categories'));
    }
    public function show($category_id)
    {
        $parent = Category::findOrFail($category_id);
        $path = ($parent->path === null) ? 1 : $parent->path .'/'. $parent->id;
        $path_explode = explode('/', $path);
        $cateDepthOne = Category::filter()
            ->where('depth', 1)
            ->where('path', $path_explode[0] ?? '')
            ->get();
        $depth = $parent->depth + 1;

        $family_cat_ids_list = Category::filter()
            ->select('id', 'title')
            ->where('parent_id', $category_id)
            ->get();

        return view('Admin.site.category.create', compact(
            'parent', 'path', 'depth', 'cateDepthOne', 'family_cat_ids_list'
        ));
    }

    public function store(Request $request)
    {
        $create = $request->only([
            "title",
            "en_title",
            "parent_id",
            "path",
            "depth",
        ]);
        $create['slug'] = $request->en_title;
        $create['displayOrder'] = $request->displayOrder ?? 1;
        $create['isActive'] = $request->isActive ?? 1;
        $category = Category::create($create);
        $category->ghorfeOnlineLists()->syncWithoutDetaching([ghorfe()->id]);
        $this->updateCategoryImages($request, $category);
        return $this->returnResponse($category);
    }
    public function edit($id)
    {
        if ($id) {
            $category = Category::find($id);
            $parent_id = $category->parent_id;
            //دسته بندی های مشابه
            $all_categories = Category::filter()->where('id', '!=', $id)->select('id', 'title')->get();
            //خدمات مشابه
            $all_services = Service::filter()->select('id', 'title')->get();
            $parents = Category::filter()->where('parent_id', 0)->get();

            //مرتبط با دسته بندی
            $family_cat_ids_list=[];
            if($category->depth > 1) {
                $path = explode('/', $category->path);
                $path = $path[0] . '/' . $path[1];
                $child_of_parent = Category::filter()->where('path', 'like', $path)->pluck('id');
            }

            if($category->depth == 2){
                $family_cat_ids_list = Category::filter()->where('path', $category->path)->get();
            }

            if($category->depth == 3){
                $family_cat_ids_list= Category::filter()->whereIn('parent_id',$child_of_parent->toArray())->get();
            }

            if($category->depth == 4){
                $childs = Category::filter()->whereIn('parent_id',$child_of_parent->toArray())->pluck('id');
                $family_cat_ids_list= Category::filter()->whereIn('parent_id',$childs->toArray())->get();
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
    public function update(Request $request, $id)
    {
        $update = $request->only([
            "title",
            "en_title",
            "parent_id",
            "path",
            "depth",
        ]);
        $update['slug'] = $request->en_title;
        $update['isActive'] = $request->has('isActive') ? $request['isActive'] : 0;
        $update['displayOrder'] = $request->has('displayOrder') ? $request['displayOrder'] : 0;
        $category = Category::find($id);
        $category->update($update);
        $this->updateCategoryImages($request,$category);
        if ($category->parent_id == 0) {
            $categories = Category::filter()->where('depth', 0)->orderByDesc('id')->paginate(15);
            return view('Admin.site.category.lists', [
                'categories' => $categories,
                'update' => 'success'
            ]);
        }
        return redirect()->back()->with(['update' => 'success']);
    }
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

    private function updateCategoryImages(Request $request, Category $category)
    {
        if (isset($request->image_path)) {
            if (File::exists(public_path($category->image_category_path))) {
                File::delete(public_path($category->image_category_path));
            }

            $category->image_category_id = $request->image_path;

            $category->save();
        }
        if (isset($request->icon_path)) {
            if (File::exists(public_path($category->icon_category_path))) {
                File::delete(public_path($category->icon_category_path));
            }
            $category->icon_category_id = $request->icon_path;
            $category->save();
        }
    }


    private function returnResponse(Category $category)
    {
        if ($category->parent_id == 0) {
            $categories = Category::filter()->where('depth', 0)->orderBy('id', 'DESC')->paginate(15);
            return view('Admin.site.category.lists', ['categories' => $categories, 'create' => 'success']);
        } else {
            return redirect('/dashboard/site/category/sub/' . $category->parent_id)->with(['create' => 'success']);
        }
    }
    /**
     * @param Request $request
     * @param $id
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application|\Illuminate\Http\RedirectResponse
     */

    private function handleGalleryFiles($files, $cat, $type)
    {
        foreach ($files as $file) {
            if (isset($file['file'])) {
                $image = Helper::moveImage($file['file'], 'category');

                if (!empty($file['gallery_id']) && $gallery = Gallery::find($file['gallery_id'])) {
                    $gallery->update([
                        'title' => $file['file_title'],
                        'image' => $image
                    ]);
                } else {
                    Gallery::create([
                        'galleryable_id' => $cat->id,
                        'galleryable_type' => Category::class,
                        'title' => $file['file_title'],
                        'image' => $image,
                        'type' => $type
                    ]);
                }
            }
        }
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
            $categories = Category::filter()->where('parent_id', $category_id)->orderBy('id', 'DESC');
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
            $categories = Category::filter()->select('id', 'title')->where('parent_id', $request->id)->get();
            $cat = Category::filter()->select('id', 'title','depth')->where('parent_id', $request->id)->first();
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
        $categories = Category::filter()
            ->select('id','title')
            ->where('isActive',1)
            ->where('id','!=',2)
            ->where('parent_id','!=',2)
            ->where('title','like','%'. $request->keyword .'%')
            ->get();
        foreach ($categories as $category) {
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
                        $attributes[$item->id] = $item->attributes()->get();
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

}
